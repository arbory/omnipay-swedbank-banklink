<?php

namespace Omnipay\SwedbankBanklink;

use Omnipay\Common\AbstractGateway;
use Omnipay\SwedbankBanklink\Messages\FetchProvidersRequest;
use Omnipay\SwedbankBanklink\Messages\FetchTransactionRequest;
use Omnipay\SwedbankBanklink\Messages\PurchaseRequest;
use Omnipay\SwedbankBanklink\Utils\HasBaseUrl;
use Omnipay\SwedbankBanklink\Utils\HasDebugLogging;

/**
 * Swedbank Payment Initiation API V3 Gateway
 *
 * This gateway implements the Swedbank E-commerce Payment Initiation API V3.
 *
 * API Endpoints:
 *   - GET /public/api/v3/agreement/providers - Get list of available banks (Step 1)
 *   - POST /public/api/v3/transactions/providers/{bic} - Initiate payment (Step 2)
 *   - GET /public/api/v3/transactions/{id}/status - Check payment status (Step 3)
 *
 * @link https://pi.swedbank.com/developer?version=public_V3
 * @link https://pi.swedbank.com/developer/api-docs/public_V3
 */
class Gateway extends AbstractGateway
{
    use HasBaseUrl;
    use HasDebugLogging;
    
    /**
     * Get gateway display name
     *
     * @return string
     */
    public function getName(): string
    {
        return 'Swedbank';
    }

    /**
     * Get default parameters
     *
     * @return array
     */
    public function getDefaultParameters(): array
    {
        return [
            'merchantId' => '',
            'country' => 'LV',
            'privateKey' => '',
            'publicKey' => '',
            'bankPublicKey' => '',
            'algorithm' => 'RS512',
            'locale' => 'en',
            'testMode' => false,
            'baseUrl' => '',
            'debugLogging' => false,
        ];
    }

    /**
     * Get merchant ID
     *
     * @return string
     */
    public function getMerchantId(): string
    {
        return $this->getParameter('merchantId');
    }

    /**
     * Set merchant ID
     *
     * @param string $value
     * @return $this
     */
    public function setMerchantId(string $value): self
    {
        return $this->setParameter('merchantId', $value);
    }

    /**
     * Get country code (LV, EE, LT)
     *
     * @return string
     */
    public function getCountry(): string
    {
        return $this->getParameter('country');
    }

    /**
     * Set country code
     *
     * @param string $value
     * @return $this
     */
    public function setCountry(string $value): self
    {
        return $this->setParameter('country', strtoupper($value));
    }

    /**
     * Get private key (PEM format)
     *
     * @return string
     */
    public function getPrivateKey(): string
    {
        return $this->getParameter('privateKey');
    }

    /**
     * Set private key
     *
     * @param string $value PEM format private key
     * @return $this
     */
    public function setPrivateKey(string $value): self
    {
        return $this->setParameter('privateKey', $value);
    }

    /**
     * Set private key from file path
     *
     * @param string $value File path to PEM format private key
     * @return $this
     * @throws \RuntimeException
     */
    public function setPrivateKeyPath(string $value): self
    {
        if (!file_exists($value)) {
            throw new \RuntimeException("Private key file not found: {$value}");
        }
        
        $keyContent = file_get_contents($value);
        if ($keyContent === false) {
            throw new \RuntimeException("Could not read private key file: {$value}");
        }
        
        return $this->setPrivateKey($keyContent);
    }

    /**
     * Get public key (PEM format)
     *
     * @return string
     */
    public function getPublicKey(): string
    {
        return $this->getParameter('publicKey');
    }

    /**
     * Set public key
     *
     * @param string $value PEM format public key
     * @return $this
     */
    public function setPublicKey(string $value): self
    {
        return $this->setParameter('publicKey', $value);
    }

    /**
     * Get bank's public key (PEM format)
     *
     * @return string
     */
    public function getBankPublicKey(): string
    {
        return $this->getParameter('bankPublicKey');
    }

    /**
     * Set bank's public key
     *
     * @param string $value PEM format bank public key
     * @return $this
     */
    public function setBankPublicKey(string $value): self
    {
        return $this->setParameter('bankPublicKey', $value);
    }

    /**
     * Set bank's public key from file path
     *
     * @param string $value File path to PEM format bank public key
     * @return $this
     * @throws \RuntimeException
     */
    public function setBankPublicKeyPath(string $value): self
    {
        if (!file_exists($value)) {
            throw new \RuntimeException("Bank public key file not found: {$value}");
        }
        
        $keyContent = file_get_contents($value);
        if ($keyContent === false) {
            throw new \RuntimeException("Could not read bank public key file: {$value}");
        }
        
        return $this->setBankPublicKey($keyContent);
    }

    /**
     * Get signing algorithm
     *
     * @return string
     */
    public function getAlgorithm(): string
    {
        return $this->getParameter('algorithm');
    }

    /**
     * Set signing algorithm
     *
     * @param string $value RS512, ES256, ES256K, ES384, or ES512
     * @return $this
     */
    public function setAlgorithm(string $value): self
    {
        return $this->setParameter('algorithm', $value);
    }

    /**
     * Get locale
     *
     * @return string
     */
    public function getLocale(): string
    {
        return $this->getParameter('locale');
    }

    /**
     * Set locale
     *
     * @param string $value Locale code: en, et, lv, lt, or ru
     * @return $this
     */
    public function setLocale(string $value): self
    {
        return $this->setParameter('locale', strtolower($value));
    }

    /**
     * Set API base URL
     *
     * @param string $value
     * @return $this
     */
    public function setBaseUrl(string $value): self
    {
        return $this->setParameter('baseUrl', $value);
    }

    /**
     * Get list of available payment providers
     *
     * API: GET /public/api/v3/agreement/providers
     *
     * @param array $options
     * @return FetchProvidersRequest
     */
    public function getProviders(array $options = []): FetchProvidersRequest
    {
        return $this->createRequest(FetchProvidersRequest::class, $options);
    }

    /**
     * Initiate a payment (Step 2)
     *
     * API: POST /public/api/v3/transactions/providers/{bic}
     *
     * @param array $options
     * @return PurchaseRequest
     */
    public function purchase(array $options = []): PurchaseRequest
    {
        return $this->createRequest(PurchaseRequest::class, $options);
    }

    /**
     * Fetch transaction status (Step 3)
     *
     * API: GET /public/api/v3/transactions/{id}/status
     *
     * @param array $options
     * @return FetchTransactionRequest
     */
    public function fetchTransaction(array $options = []): FetchTransactionRequest
    {
        return $this->createRequest(FetchTransactionRequest::class, $options);
    }

    /**
     * Complete purchase (handle return from bank)
     * Alias for fetchTransaction
     *
     * @param array $options
     * @return FetchTransactionRequest
     */
    public function completePurchase(array $options = []): FetchTransactionRequest
    {
        return $this->fetchTransaction($options);
    }
}
