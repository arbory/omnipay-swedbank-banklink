<?php

namespace Omnipay\SwedbankBanklink\Tests;

use Omnipay\SwedbankBanklink\Gateway;
use Omnipay\SwedbankBanklink\Messages\FetchProvidersRequest;
use Omnipay\SwedbankBanklink\Messages\PurchaseRequest;
use Omnipay\SwedbankBanklink\Messages\FetchTransactionRequest;
use PHPUnit\Framework\TestCase;

class FunctionalTest extends TestCase
{
    private $gateway;

    protected function setUp(): void
    {
        $this->gateway = new Gateway(null, null);
        
        $this->gateway->initialize([
            'merchantId' => 'TEST_MERCHANT_001',
            'country' => 'LV',
            'privateKeyPath' => __DIR__ . '/Fixtures/test_private.key',
            'bankPublicKeyPath' => __DIR__ . '/Fixtures/bank_public.key',
            'algorithm' => 'RS512',
            'testMode' => true,
        ]);
    }

    public function testGatewayInitialization()
    {
        $this->assertEquals('Swedbank', $this->gateway->getName());
        $this->assertEquals('TEST_MERCHANT_001', $this->gateway->getMerchantId());
        $this->assertEquals('LV', $this->gateway->getCountry());
        $this->assertEquals('RS512', $this->gateway->getAlgorithm());
        $this->assertTrue($this->gateway->getTestMode());
    }

    public function testGatewaySettings()
    {
        $this->gateway->setMerchantId('NEW_MERCHANT');
        $this->assertEquals('NEW_MERCHANT', $this->gateway->getMerchantId());

        $this->gateway->setCountry('EE');
        $this->assertEquals('EE', $this->gateway->getCountry());

        $this->gateway->setAlgorithm('ES256');
        $this->assertEquals('ES256', $this->gateway->getAlgorithm());

        $this->gateway->setLocale('lv');
        $this->assertEquals('lv', $this->gateway->getLocale());

        $this->gateway->setTestMode(false);
        $this->assertFalse($this->gateway->getTestMode());
    }

    public function testGetProvidersRequest()
    {
        $request = $this->gateway->getProviders();
        
        $this->assertInstanceOf(FetchProvidersRequest::class, $request);
        $this->assertEquals('TEST_MERCHANT_001', $request->getMerchantId());
        $this->assertEquals('LV', $request->getCountry());
        $this->assertEquals('GET', $request->getHttpMethod());
        // V3 endpoint: GET /public/api/v3/agreement/providers
        $this->assertStringContainsString('/public/api/v3/agreement/providers', $request->getEndpoint());
    }

    public function testPurchaseRequest()
    {
        $request = $this->gateway->purchase([
            'amount' => '10.00',
            'currency' => 'EUR',
            'locale' => 'en',
            'returnUrl' => 'https://example.com/return',
            'notificationUrl' => 'https://example.com/notify',
            'description' => 'Payment for order',
            'provider' => 'HABALT22',  // BIC code (V3 requires BIC, not bank name)
        ]);

        $this->assertInstanceOf(PurchaseRequest::class, $request);
        $this->assertEquals('10.00', $request->getAmount());
        $this->assertEquals('EUR', $request->getCurrency());
        $this->assertEquals('en', $request->getLocale());
        $this->assertEquals('https://example.com/return', $request->getReturnUrl());
        $this->assertEquals('https://example.com/notify', $request->getNotificationUrl());
        $this->assertEquals('Payment for order', $request->getDescription());
        $this->assertEquals('HABALT22', $request->getProvider());
        $this->assertEquals('POST', $request->getHttpMethod());
    }

    public function testFetchTransactionRequest()
    {
        $request = $this->gateway->fetchTransaction([
            'transactionReference' => 'c625052f07004261aee27a8e069b0a5drg23',
        ]);

        $this->assertInstanceOf(FetchTransactionRequest::class, $request);
        $this->assertEquals('c625052f07004261aee27a8e069b0a5drg23', $request->getTransactionReference());
        $this->assertEquals('GET', $request->getHttpMethod());
        // V3 endpoint format: GET /public/api/v3/transactions/{id}/status
        $this->assertStringContainsString('/public/api/v3/transactions/', $request->getEndpoint());
        $this->assertStringContainsString('/status', $request->getEndpoint());
    }

    public function testCompletePurchaseRequest()
    {
        $request = $this->gateway->completePurchase([
            'transactionReference' => 'c625052f07004261aee27a8e069b0a5drg23',
        ]);

        $this->assertInstanceOf(FetchTransactionRequest::class, $request);
        $this->assertEquals('c625052f07004261aee27a8e069b0a5drg23', $request->getTransactionReference());
    }

    public function testGatewaySupport()
    {
        $this->assertTrue($this->gateway->supportsPurchase());
        $this->assertTrue($this->gateway->supportsCompletePurchase());
        $this->assertFalse($this->gateway->supportsAcceptNotification());
    }

    public function testGatewayBaseUrlProduction()
    {
        $prodUrl = getenv('SWEDBANK_GATEWAY_URL_PROD');
        $this->gateway->setBaseUrl($prodUrl);
        $this->gateway->setTestMode(false);
        $reflection = new \ReflectionClass($this->gateway);
        $method = $reflection->getMethod('getBaseUrl');
        $method->setAccessible(true);

        $baseUrl = $method->invoke($this->gateway);
        $this->assertEquals($prodUrl, $baseUrl);
    }

    public function testGatewayBaseUrlSandbox()
    {
        $sandboxUrl = getenv('SWEDBANK_GATEWAY_URL_SANDBOX');
        $this->gateway->setBaseUrl($sandboxUrl);
        $this->gateway->setTestMode(true);
        $reflection = new \ReflectionClass($this->gateway);
        $method = $reflection->getMethod('getBaseUrl');
        $method->setAccessible(true);
        $baseUrl = $method->invoke($this->gateway);
        $this->assertEquals($sandboxUrl, $baseUrl);
    }
}
