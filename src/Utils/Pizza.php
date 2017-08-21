<?php

namespace Omnipay\SwedbankBanklink\Utils;
/**
 * Pizza protocol helper functions
 *
 * @package Omnipay\SwedbankBanklink\Utils
 */
class Pizza
{
    // Returns base64 encoded control code
    public static function generateControlCode(array $data, $encoding, $privateCertPath)
    {
        $hash = self::createHash($data, $encoding);

        // Compute controlCode
        //TODO: add passphrase?
        $certContent = file_get_contents($privateCertPath);
        $privateKey = openssl_get_privatekey($certContent);
        openssl_sign($hash, $controlCode, $privateKey);
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
    public static function isValidControlCode(array $data, $controlCodeEncoded, $privateCertPath, $encoding)
    {
        $hash = self::createHash($data, $encoding);
        $signature = base64_decode($controlCodeEncoded);
        $certContent = file_get_contents($privateCertPath);
        $privateKey = openssl_get_privatekey($certContent);
        // Public key as PEM string
        $pemPublicKey = openssl_pkey_get_details($privateKey)['key'];
        $publicKey = openssl_get_publickey($pemPublicKey);

        if($publicKey === false){
            throw new \RuntimeException('Certificate error :' . openssl_error_string());
        }

        $result = openssl_verify($hash, $signature, $publicKey);

        openssl_free_key($publicKey);
        openssl_free_key($privateKey);

        if($result !== 1 && $result !== 0){
            // OpenSSL error, problem with pem certificate
            throw new \RuntimeException('Verification error :' . openssl_error_string());
        }

        return boolval($result);
    }

    /**
     * Test encoding/decoding by comparing results, this will allow also help debugging certificate file problems
     * @param array $data
     * @param       $privateCertPath
     * @param       $encoding
     * @return bool
     */
    public static function test(array $data, $privateCertPath, $encoding)
    {
        $result = self::isValidControlCode($data, self::generateControlCode($data, $encoding, $privateCertPath), $privateCertPath, $encoding);
        return $result;
    }

}