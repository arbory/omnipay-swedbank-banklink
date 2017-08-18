<?php

namespace Omnipay\SwedbankBanklink\Utils;
/**
 * Pizza protocol helper functions
 *
 * @package Omnipay\SwedbankBanklink\Utils
 */
class Pizza
{
    public static function generateControlCode(array $data, $encoding, $privateCertPath)
    {
        $hash = '';
        foreach ($data as $fieldName => $fieldValue) {
            $content = $data[$fieldName];
            $length = mb_strlen($content, $encoding);
            $hash .= str_pad($length, 3, '0', STR_PAD_LEFT) . $content;
        }

        // Compute controlCode
        //TODO: add passphrase?
        $certContent = file_get_contents($privateCertPath);
        $privateKey = openssl_pkey_get_private($certContent);
        openssl_sign($hash, $controlCode, $privateKey);
        openssl_free_key($privateKey);

        return $controlCode;
    }

    /**
     * Verifies if control code is valid for data
     * @param $data array key/value pairs
     * @param $controlCodeEncoded
     * @param $privateCertPath
     * @return bool
     * @throws \RuntimeException
     */
    public static function isValidControlCode(array $data, $controlCodeEncoded, $privateCertPath)
    {
        $signature = base64_decode($controlCodeEncoded);
        $cert = file_get_contents($privateCertPath);
        $result = openssl_verify($data, $signature, $cert);

        if($result === -1){
            // OpenSSL error, problem with pem certificate
            throw new \RuntimeException('Verification Error :' . openssl_error_string());
        }

        return boolval($result);
    }

}