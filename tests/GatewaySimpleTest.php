<?php

namespace Omnipay\SwedbankBanklink\Tests;

use Omnipay\SwedbankBanklink\Gateway;
use Omnipay\SwedbankBanklink\Messages\FetchProvidersRequest;
use Omnipay\SwedbankBanklink\Messages\PurchaseRequest;
use Omnipay\SwedbankBanklink\Messages\FetchTransactionRequest;
use Omnipay\Tests\TestCase;

class GatewaySimpleTest extends TestCase
{
    private $gateway;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gateway = new Gateway($this->getHttpClient(), $this->getHttpRequest());
        
        $this->gateway->initialize([
            'merchantId' => 'TEST_MERCHANT_001',
            'country' => 'LV',
            'privateKeyPath' => __DIR__ . '/Fixtures/test_private.key',
            'bankPublicKeyPath' => __DIR__ . '/Fixtures/bank_public.key',
            'algorithm' => 'RS512',
            'testMode' => true,
        ]);
    }

    public function testGetName()
    {
        $this->assertEquals('Swedbank', $this->gateway->getName());
    }

    public function testDefaultParameters()
    {
        $this->assertEquals('TEST_MERCHANT_001', $this->gateway->getMerchantId());
        $this->assertEquals('LV', $this->gateway->getCountry());
        $this->assertEquals('RS512', $this->gateway->getAlgorithm());
        $this->assertEquals('en', $this->gateway->getLocale());
        $this->assertTrue($this->gateway->getTestMode());
    }

    public function testSupportsPurchase()
    {
        $this->assertTrue($this->gateway->supportsPurchase());
    }

    public function testSupportsCompletePurchase()
    {
        $this->assertTrue($this->gateway->supportsCompletePurchase());
    }
}
