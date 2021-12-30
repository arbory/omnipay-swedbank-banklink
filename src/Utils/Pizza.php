<?php

namespace Omnipay\SwedbankBanklink\Utils;

/**
 * Pizza protocol helper functions
 *
 * @package Omnipay\SwedbankBanklink\Utils
 */
class Pizza
{

    const SIGNATURE_ALGORITHM = OPENSSL_ALGO_SHA512;

    // Returns base64 encoded control code
    public static function generateControlCode(array $data, $encoding, $privateCertPath, $passPhrase)
    {
        $hash = self::createHash($data, $encoding);

        // Compute controlCode
        $certContent = file_get_contents($privateCertPath);
        $privateKey = openssl_get_privatekey($certContent, $passPhrase);
        openssl_sign($hash, $controlCode, $privateKey, self::SIGNATURE_ALGORITHM);
        openssl_free_key($privateKey);

        return base64_encode($controlCode);
    }

    public static function createHash(array $data, $encoding)
    {
        $hash = '';
        foreach ($data as $fieldName => $fieldValue) {
            $content = $data[$fieldName];
            $length = mb_strlen($content, $encoding);
            $hash .= str_pad($length, 3, '0', STR_PAD_LEFT) . $content;
        }
        return $hash;
    }

    /**
     * Verifies if control code is valid for data
     * @param $data array key/value pairs
     * @param $controlCodeEncoded
     * @param $privateCertPath
     * @return bool
     * @throws \RuntimeException
     */
    public static function isValidControlCode(array $data, $signatureEncoded, $publicCertPath, $encoding)
    {
        $hash = self::createHash($data, $encoding);
        $signature = base64_decode($signatureEncoded);
        $certContent = file_get_contents($publicCertPath);
        $publicKey = openssl_get_publickey($certContent);

        if ($publicKey === false) {
            throw new \RuntimeException('Certificate error :' . openssl_error_string());
        }

        $result = openssl_verify($hash, $signature, $publicKey);

        openssl_free_key($publicKey);

        return boolval($result);
    }
}
