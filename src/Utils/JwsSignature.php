<?php

namespace Omnipay\SwedbankBanklink\Utils;

use RuntimeException;

/**
 * JWS (JSON Web Signature) utility for Swedbank V3 API
 * 
 * Implements RFC7515 JWS Detached signature format
 */
class JwsSignature
{
    private const ALLOWED_ALGORITHMS = ['RS512', 'ES256', 'ES256K', 'ES384', 'ES512'];
    
    private const OPENSSL_ALGORITHMS = [
        'RS512' => OPENSSL_ALGO_SHA512,
        'ES256' => OPENSSL_ALGO_SHA256,
        'ES256K' => OPENSSL_ALGO_SHA256,
        'ES384' => OPENSSL_ALGO_SHA384,
        'ES512' => OPENSSL_ALGO_SHA512,
    ];

    /**
     * Generate JWS Detached signature for request
     *
     * @param string $payload JSON payload to sign
     * @param string $url Request URL
     * @param string $merchantId Merchant ID
     * @param string $country Country code (LV, EE, LT)
     * @param string $privateKey Private key in PEM format
     * @param string $algorithm Signing algorithm (default: RS512)
     * @return string JWS Detached signature (xxxxx..zzzzz)
     */
    public static function sign(
        string $payload,
        string $url,
        string $merchantId,
        string $country,
        string $privateKey,
        string $algorithm = 'RS512'
    ): string {
        if (!in_array($algorithm, self::ALLOWED_ALGORITHMS, true)) {
            throw new RuntimeException("Unsupported algorithm: {$algorithm}");
        }

        // Create JWS header
        $header = [
            'b64' => false,
            'crit' => ['b64'],
            'iat' => time(),
            'alg' => $algorithm,
            'url' => $url,
            'kid' => "{$country}:{$merchantId}",
        ];

        $encodedHeader = self::base64UrlEncode(json_encode($header, JSON_UNESCAPED_SLASHES));

        // Create signing input: base64url(header) + '.' + payload
        $signingInput = $encodedHeader . '.' . $payload;

        // Sign the input
        $signature = self::signData($signingInput, $privateKey, $algorithm);
        $encodedSignature = self::base64UrlEncode($signature);

        // Return JWS Detached format: header..signature (payload is detached)
        return $encodedHeader . '..' . $encodedSignature;
    }

    /**
     * Verify JWS Detached signature from response
     *
     * @param string $jwsDetached JWS Detached signature (xxxxx..zzzzz)
     * @param string $payload Response payload
     * @param string $publicKey Bank's public key in PEM format
     * @param int $maxAgeSeconds Maximum age of signature in seconds (default: 120)
     * @return bool True if signature is valid
     */
    public static function verify(
        string $jwsDetached,
        string $payload,
        string $publicKey,
        int $maxAgeSeconds = 120
    ): bool {
        $parts = explode('..', $jwsDetached);
        
        if (count($parts) !== 2) {
            throw new RuntimeException('Invalid JWS Detached format - expected "header..signature" but got format: ' . substr($jwsDetached, 0, 50));
        }

        [$encodedHeader, $encodedSignature] = $parts;

        // Decode header
        $headerJson = self::base64UrlDecode($encodedHeader);
        $header = json_decode($headerJson, true);

        if (!is_array($header)) {
            throw new RuntimeException('Invalid JWS header: ' . $headerJson);
        }

        // Validate header structure
        if (!isset($header['alg'], $header['iat'])) {
            throw new RuntimeException('Missing required header fields in JWS: ' . json_encode($header));
        }

        // Check signature age for non-notification responses
        if ($maxAgeSeconds > 0) {
            $age = abs(time() - $header['iat']);
            if ($age > $maxAgeSeconds) {
                throw new RuntimeException("Signature too old: {$age} seconds (iat: {$header['iat']}, now: " . time() . ")");
            }
        }

        // Reconstruct signing input
        $signingInput = $encodedHeader . '.' . $payload;
        $signature = self::base64UrlDecode($encodedSignature);

        // Verify signature - include debug info if it fails
        $result = self::verifyData($signingInput, $signature, $publicKey, $header['alg']);
        
        if (!$result) {
            // Add debugging - log the hash of what was verified to help diagnose issues
            error_log('JWS Signature verification failed - signing input length: ' . strlen($signingInput) . 
                     ', payload length: ' . strlen($payload) . 
                     ', signature length: ' . strlen($signature) . 
                     ', algorithm: ' . $header['alg']);
        }
        
        return $result;
    }

    /**
     * Sign data using private key
     *
     * @param string $data Data to sign
     * @param string $privateKey Private key in PEM format
     * @param string $algorithm Algorithm to use
     * @return string Binary signature
     */
    private static function signData(string $data, string $privateKey, string $algorithm): string
    {
        $key = openssl_pkey_get_private($privateKey);
        
        if ($key === false) {
            throw new RuntimeException('Invalid private key: ' . openssl_error_string());
        }

        $opensslAlg = self::OPENSSL_ALGORITHMS[$algorithm] ?? OPENSSL_ALGO_SHA512;
        
        $signature = '';
        $success = openssl_sign($data, $signature, $key, $opensslAlg);
        
        openssl_free_key($key);

        if (!$success) {
            throw new RuntimeException('Failed to sign data: ' . openssl_error_string());
        }

        return $signature;
    }

    /**
     * Verify data signature using public key
     *
     * @param string $data Signed data
     * @param string $signature Binary signature (may be in JWS raw format for ECDSA)
     * @param string $publicKey Public key in PEM format (can be X.509 certificate or PKCS#8)
     * @param string $algorithm Algorithm used
     * @return bool True if signature is valid
     */
    private static function verifyData(
        string $data,
        string $signature,
        string $publicKey,
        string $algorithm
    ): bool {
        // OpenSSL can handle both raw public keys and X.509 certificates
        $key = openssl_pkey_get_public($publicKey);
        
        if ($key === false) {
            throw new RuntimeException('Invalid public key format: ' . openssl_error_string() . 
                                     '. Key should be in PEM format (-----BEGIN CERTIFICATE----- or -----BEGIN PUBLIC KEY-----)');
        }

        $opensslAlg = self::OPENSSL_ALGORITHMS[$algorithm] ?? OPENSSL_ALGO_SHA512;
        
        // For ECDSA algorithms, convert JWS format (r||s raw concat) to ASN.1 DER format if needed
        $convertedSignature = $signature;
        if (in_array($algorithm, ['ES256', 'ES256K', 'ES384', 'ES512'])) {
            $convertedSignature = self::convertEcdsaSignatureToDer($signature, $algorithm);
        }
        
        $result = openssl_verify($data, $convertedSignature, $key, $opensslAlg);
        
        openssl_free_key($key);

        if ($result === -1) {
            throw new RuntimeException('Error verifying signature with algorithm ' . $algorithm . ': ' . openssl_error_string());
        }

        return $result === 1;
    }

    /**
     * Convert ECDSA signature from JWS raw format (r||s) to DER format
     *
     * @param string $signature Raw signature bytes in r||s format
     * @param string $algorithm ECDSA algorithm (ES256, ES256K, ES384, ES512)
     * @return string Signature in DER format
     */
    private static function convertEcdsaSignatureToDer(string $signature, string $algorithm): string
    {
        // Determine the expected length for r and s based on algorithm
        $expectedLength = match($algorithm) {
            'ES256', 'ES256K' => 32,  // 256 bits = 32 bytes
            'ES384' => 48,            // 384 bits = 48 bytes
            'ES512' => 66,            // 521 bits = 66 bytes
            default => 32
        };
        
        // If signature is not the expected raw format, assume it's already DER encoded
        if (strlen($signature) !== $expectedLength * 2) {
            // Try to detect if it's already DER encoded (starts with 0x30 for SEQUENCE)
            if (!empty($signature) && ord($signature[0]) === 0x30) {
                return $signature;
            }
            // Otherwise return as-is and let OpenSSL handle it
            return $signature;
        }
        
        // Extract r and s from raw format
        $r = substr($signature, 0, $expectedLength);
        $s = substr($signature, $expectedLength, $expectedLength);
        
        // Convert r and s to DER format
        $rDer = self::intToDerInteger($r);
        $sDer = self::intToDerInteger($s);
        
        // Return as SEQUENCE of two INTEGERs
        $sequence = $rDer . $sDer;
        return chr(0x30) . chr(strlen($sequence)) . $sequence;
    }

    /**
     * Convert a big integer (as binary string) to DER INTEGER format
     *
     * @param string $int Binary representation of integer
     * @return string DER encoded INTEGER
     */
    private static function intToDerInteger(string $int): string
    {
        // Remove leading zeros, but keep one zero if high bit is set (for positive numbers)
        $int = ltrim($int, "\x00");
        
        // If empty or high bit is set, prepend 0x00 to indicate positive number
        if (empty($int) || (ord($int[0]) & 0x80) !== 0) {
            $int = "\x00" . $int;
        }
        
        // DER INTEGER: tag (0x02) + length + content
        return chr(0x02) . chr(strlen($int)) . $int;
    }


    /**
     * Base64 URL encode
     *
     * @param string $data Data to encode
     * @return string Base64 URL encoded string
     */
    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Base64 URL decode
     *
     * @param string $data Data to decode
     * @return string Decoded string
     */
    private static function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }

    /**
     * Parse JWS header from detached signature
     *
     * @param string $jwsDetached JWS Detached signature
     * @return array Header array
     */
    public static function parseHeader(string $jwsDetached): array
    {
        $parts = explode('..', $jwsDetached);
        
        if (count($parts) !== 2) {
            throw new RuntimeException('Invalid JWS Detached format');
        }

        $headerJson = self::base64UrlDecode($parts[0]);
        $header = json_decode($headerJson, true);

        if (!is_array($header)) {
            throw new RuntimeException('Invalid JWS header');
        }

        return $header;
    }

    /**
     * Validate and get information about the bank's public key
     *
     * @param string $publicKey Public key in PEM format
     * @return array Array with key information for debugging
     */
    public static function validateCertificate(string $publicKey): array
    {
        $key = openssl_pkey_get_public($publicKey);
        
        if ($key === false) {
            return [
                'valid' => false,
                'error' => 'Invalid public key format: ' . openssl_error_string()
            ];
        }
        
        $details = openssl_pkey_get_details($key);
        openssl_free_key($key);
        
        if ($details === false) {
            return [
                'valid' => false,
                'error' => 'Could not extract key details'
            ];
        }
        
        $result = [
            'valid' => true,
            'type' => $details['type'],
            'bits' => $details['bits'] ?? 'unknown'
        ];
        
        // Check if it's a certificate
        if (strpos($publicKey, '-----BEGIN CERTIFICATE-----') !== false) {
            $cert = openssl_x509_parse($publicKey);
            if ($cert !== false) {
                $result['subject'] = $cert['subject'] ?? [];
                $result['issuer'] = $cert['issuer'] ?? [];
                $result['validFrom'] = date('Y-m-d H:i:s', $cert['validFrom_time_t'] ?? 0);
                $result['validTo'] = date('Y-m-d H:i:s', $cert['validTo_time_t'] ?? 0);
                $result['isExpired'] = time() > ($cert['validTo_time_t'] ?? 0);
            }
        }
        
        return $result;
    }

    /**
     * Inspect and decode a JWS Detached signature for debugging
     *
     * @param string $jwsDetached JWS Detached signature
     * @return array Array with header info and diagnostic data
     */
    public static function inspectSignature(string $jwsDetached): array
    {
        try {
            $parts = explode('..', $jwsDetached);
            
            if (count($parts) !== 2) {
                return [
                    'valid_format' => false,
                    'error' => 'Invalid JWS Detached format - expected "header..signature"',
                    'parts_count' => count($parts),
                    'jws_preview' => substr($jwsDetached, 0, 100) . (strlen($jwsDetached) > 100 ? '...' : '')
                ];
            }
            
            [$encodedHeader, $encodedSignature] = $parts;
            
            $headerJson = self::base64UrlDecode($encodedHeader);
            $header = json_decode($headerJson, true);
            
            return [
                'valid_format' => true,
                'header' => $header ?? [],
                'header_json' => $headerJson,
                'signature_length' => strlen(self::base64UrlDecode($encodedSignature)),
                'signature_length_base64' => strlen($encodedSignature),
                'encoded_header_length' => strlen($encodedHeader)
            ];
        } catch (\Throwable $e) {
            return [
                'error' => 'Exception while inspecting signature: ' . $e->getMessage()
            ];
        }
    }
}
