<?php

namespace Omnipay\SwedbankBanklink;

use Omnipay\Tests\GatewayTestCase;

class GatewayTest extends GatewayTestCase
{
    /**
     * @var \Omnipay\SwedbankBanklink\Gateway
     */
    protected $gateway;

    /**
     * @var array
     */
    protected $options;

    public function setUp()
    {
        parent::setUp();

        $this->gateway = new Gateway($this->getHttpClient(), $this->getHttpRequest());

        $this->options = array(
            'merchantId' => '1',
            'returnUrl' => 'http://localhost:8080/omnipay/banklink/',
            'privateCertificatePath' => 'tests/Fixtures/key.pem',
            'publicCertificatePath' => 'tests/Fixtures/key.pub',
            'transactionReference' => 'abc123',
            'description' => 'purchase description',
            'amount' => '10.00',
            'currency' => 'EUR',
        );
    }

    public function testPurchaseSuccess()
    {
        $response = $this->gateway->purchase($this->options)->send();

        $this->assertInstanceOf('\Omnipay\SwedbankBanklink\Messages\PurchaseResponse', $response);
        $this->assertFalse($response->isPending());
        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->isRedirect());
        $this->assertEquals('https://www.swedbank.lv/banklink/', $response->getRedirectUrl());

        $this->assertEquals(array(
            'VK_SERVICE' => 1002,
            'VK_VERSION' => '008',
            'VK_SND_ID' => '1',
            'VK_STAMP' => 'abc123',
            'VK_AMOUNT' => '10.00',
            'VK_CURR' => 'EUR',
            'VK_REF' => 'abc123',
            'VK_MSG' => 'purchase description',
            'VK_MAC' => 'vizso1yFuk6oSGjen3oEZLst01BoQin1Y8yuCLWLtoQ6GVRJueMDJVqw4fUZ+Zt17JgdTD/7kx/1USMlUOG2gQHQBVeTt2iMyl9QKjoBb9zGTGMSTiI35MnddIpaO0oLoMZ9PRuvgUzyVo8Sq2Ojuet9ZNjyZNFe55SrxYJntXXoIV3CUBk+WTMTIdlytem3f51rj8O8xE0VoUrJaamqv8nFoVicIPdKJre8Fu1uNnmKWUC9kA0Fj7rz1M3t8jUaoHuIlPIeYvSOpkc64RWXw4E7b4ffDFdGpf5d1OYwHp9gnHBiRC85jUm1ADGUTe2ZZg5IaUUjn8heGfSE3bFyMQ==',
            'VK_RETURN' => 'http://localhost:8080/omnipay/banklink/',
            'VK_LANG' => 'LAT',
            'VK_ENCODING' => 'UTF-8',
        ), $response->getData());
    }

    public function testPurchaseCompleteSuccess()
    {
        $postData = array(
            'VK_SERVICE' => '1101',
            'VK_VERSION' => '008',
            'VK_SND_ID' => 'HP',
            'VK_REC_ID' => 'REFEREND',
            'VK_STAMP' => 'abc123',
            'VK_T_NO' => '169',
            'VK_AMOUNT' => '10.00',
            'VK_CURR' => 'EUR',
            'VK_REC_ACC' => 'XXXXXXXXXX',
            'VK_REC_NAME' => 'Shop',
            'VK_SND_ACC' => 'XXXXXXXXXXXX',
            'VK_SND_NAME' => 'John Mayer',
            'VK_REF' => 'abc123',
            'VK_MSG' => 'Payment for order 1231223',
            'VK_T_DATE' => '10.03.2019',
            'VK_LANG' => 'LAT',
            'VK_AUTO' => 'N',
            'VK_ENCODING' => 'UTF-8',
            'VK_MAC' => 'uHB+cjwJa7O1eCo/mwh81aAy9esSTEmExdKvWDxZrK3pn3l/Utr5Sy1vnDUzJSWGq24tBTA3saCmoVZON1FW1XRIwFyd04rhEXG2VwX+zLTzUKOEM+K98Xzs2HX8jAytjlsF2XlJYbxNM3hBej8MndvRHaBYNCl6h4Lv/y9js2z05mi2tTHKamK4w5kVOTDkV1Za0Aafx2rFoQMMqFmE+26TUcUx+Q8IvJ6vGM5+VRnCsCKzQxzN4YYftRFJo+8SHefsdhNirr10UHbkwJFNzhyuKjeEkOglCaEcq+syOhY9MDQ58AVY50vs1/q42dXicv+fTNFvu6tglSNDQJ7Ikg=='
        );

        $this->getHttpRequest()->query->replace($postData);

        $response = $this->gateway->completePurchase($this->options)->send();

        $this->assertFalse($response->isPending());
        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('abc123', $response->getTransactionReference());
    }

    public function testPurchaseCompleteFailed()
    {
        $postData = array(
            'VK_SERVICE' => '1901',
            'VK_VERSION' => '008',
            'VK_SND_ID' => 'HP',
            'VK_REC_ID' => 'REFEREND',
            'VK_STAMP' => 'abc123',
            'VK_CURR' => 'EUR',
            'VK_REF' => 'abc123',
            'VK_MSG' => 'Payment for order 1231223',
            'VK_LANG' => 'LAT',
            'VK_AUTO' => 'N',
            'VK_ENCODING' => 'UTF-8',
            'VK_MAC' => 'dQJAGsSK+GVzCBJA81UKOZYg/uYXjcjdRpejg1Kepy4JW2cGJeUqOnZEWI9IeyfD5r6hXJXKd6SthGi5FXeuKtPUyR3KqpjAzp7IYVav/zM2SiZ92qWt6b1LPN4UGIy6sHPYK6w6pqySwSNYOLBCoDeYxW6fQS8I44558h2xBmC21veYyu0VrH4WDoUUWYFOmcCxemW+WZXso7Kn0C4VMCsHAP+5PRy60cdmOK0B6gtdMAIOht0hvsYq7frE89Aopc4zE8FmnhKBAkHTzJ0IrnH0/72Eaa22otAJR+8ORSSEMzyG7xtyvUubPKWJonBA0lcRbCMv74f8uNjmDwByWA=='
        );

        $this->getHttpRequest()->query->replace($postData);

        $response = $this->gateway->completePurchase($this->options)->send();

        $this->assertFalse($response->isPending());
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertSame('abc123', $response->getTransactionReference());
    }

    public function testPurchaseCompleteFailedWithForgedSignature()
    {
        $postData = array(
            'VK_SERVICE' => '1901',
            'VK_VERSION' => '008',
            'VK_SND_ID' => 'HP',
            'VK_REC_ID' => 'REFEREND',
            'VK_STAMP' => 'abc123',
            'VK_CURR' => 'EUR',
            'VK_REF' => 'abc123',
            'VK_MSG' => 'Payment for order 1231223',
            'VK_LANG' => 'LAT',
            'VK_AUTO' => 'N',
            'VK_ENCODING' => 'UTF-8',
            'VK_MAC' => 'FORGED_SIGNATURE'
        );

        $this->getHttpRequest()->query->replace($postData);

        $this->expectException(\Omnipay\Common\Exception\InvalidRequestException::class);

        $response = $this->gateway->completePurchase($this->options)->send();
    }

    public function testPurchaseCompleteFailedWithInvalidRequest()
    {
        $postData = array(
            'some_param' => 'x',
        );

        $this->getHttpRequest()->query->replace($postData);

        $this->expectException(\Omnipay\Common\Exception\InvalidRequestException::class);

        $response = $this->gateway->completePurchase($this->options)->send();
    }
}
