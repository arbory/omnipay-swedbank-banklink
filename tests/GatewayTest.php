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

    public function setUp(): void
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

        // regenerate new bank mac with: \Omnipay\SwedbankBanklink\Utils\Pizza::generateControlCode($data, 'UTF-8', 'tests/Fixtures/key.pem', '')
    }

    public function testPurchaseSuccess()
    {
        $options = $this->options;
        $options['dateTime'] = \DateTime::createFromFormat('Y-m-d\TH:i:s+', '2017-03-03T09:06:41.187');

        $response = $this->gateway->purchase($options)->send();

        $this->assertInstanceOf('\Omnipay\SwedbankBanklink\Messages\PurchaseResponse', $response);
        $this->assertFalse($response->isPending());
        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->isRedirect());
        $this->assertTrue($response->isTransparentRedirect());
        $this->assertEquals('POST', $response->getRedirectMethod());
        $this->assertEquals('https://www.swedbank.lv/banklink/', $response->getRedirectUrl());

        $this->assertEquals(array(
            'VK_SERVICE' => '1012',
            'VK_VERSION' => '009',
            'VK_SND_ID' => '1',
            'VK_STAMP' => 'abc123',
            'VK_AMOUNT' => '10.00',
            'VK_CURR' => 'EUR',
            'VK_REF' => '',
            'VK_MSG' => 'purchase description',
            'VK_MAC' => 'lrqLBns945Rn8CJ/BCZYOc/jFWPvWxcZ7R7QP0/z1OA8XuCUFnkfqAHiqw4InHXYKLhBEoSUkpl4vByZf5alRItnkAiG76zBK6HbHObuHBbxTjzvhVgNyJHhM0KC/9sOcRMNzZnE5pSqn2+wjvxon8k6ovcbbleueQ9aJrrKuLTKK7ltteBr3xXzkFn/SEk+3HKw6mPxmA+DbRsyIZG3oID9eLaqu2hVTxU8brOB7tUXUK+FUOHW/6f+ZYxQKOiSFJfVAOhJk0BvLU+7NA4IOfjoHB1LKDC91rXDtHYsR5CAc38DHgKgHVAfRXRtCG3EzvX+lwKbreaK8dTPfdtBlQ==',
            'VK_RETURN' => 'http://localhost:8080/omnipay/banklink/',
            'VK_LANG' => 'LAT',
            'VK_ENCODING' => 'UTF-8',
            'VK_CANCEL' => 'http://localhost:8080/omnipay/banklink/',
            'VK_DATETIME' => '2017-03-03T09:06:41+0000',
        ), $response->getData());

        $this->assertEquals($response->getData(), $response->getRedirectData());
    }

    public function testPurchaseSuccessWithPassphrasedPrivateKey()
    {
        $options = $this->options;
        $options['privateCertificatePath'] = 'tests/Fixtures/key_with_passphrase.pem';
        $options['privateCertificatePassphrase'] = 'foobar';
        $options['dateTime'] = \DateTime::createFromFormat('Y-m-d\TH:i:s+', '2017-03-03T09:06:41.187');

        $response = $this->gateway->purchase($options)->send();

        $this->assertInstanceOf('\Omnipay\SwedbankBanklink\Messages\PurchaseResponse', $response);
        $this->assertFalse($response->isPending());
        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->isRedirect());
        $this->assertTrue($response->isTransparentRedirect());
        $this->assertEquals('POST', $response->getRedirectMethod());
        $this->assertEquals('https://www.swedbank.lv/banklink/', $response->getRedirectUrl());

        $this->assertEquals(array(
            'VK_SERVICE' => '1012',
            'VK_VERSION' => '009',
            'VK_SND_ID' => '1',
            'VK_STAMP' => 'abc123',
            'VK_AMOUNT' => '10.00',
            'VK_CURR' => 'EUR',
            'VK_REF' => '',
            'VK_MSG' => 'purchase description',
            'VK_MAC' => 'fNIgIQ/bxc4Ld0ZRdjVTHjLCOyN45DweCzter1RBRd4BK3wlPFSbaK1b2LPFM4+b6btLK4ND53yigddZhu18i7pudWVRpjlTTC3qq7OIacqMojVRyekr6ZevMmSOMKrO5GKR9D6ENBK9IZEmhhW025CXcMeazPKu3R8SRLqQyt8zGwb9+8M5YKIv0lcIJwTQ3RFId/KaEfDyoeu3ZbPpZwkM+/WmJgRr9//fvsG7Jxmf57KsqbNA/DSAQMDjm+jZ+O27tuFEvgtgVKdwgMOhzJIChJn2daghRIhnyn3hqMBtLksIJ0QYG7J/t6xJPiogv5IjnXvc9ejrActIKn357w==',
            'VK_RETURN' => 'http://localhost:8080/omnipay/banklink/',
            'VK_LANG' => 'LAT',
            'VK_ENCODING' => 'UTF-8',
            'VK_CANCEL' => 'http://localhost:8080/omnipay/banklink/',
            'VK_DATETIME' => '2017-03-03T09:06:41+0000',
        ), $response->getData());

        $this->assertEquals($response->getData(), $response->getRedirectData());
    }

    public function testPurchaseCompleteSuccessWithPOST()
    {
        $postData = array(
            'VK_SERVICE' => '1111',
            'VK_VERSION' => '009',
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
            'VK_T_DATETIME' => '2020-03-13T07:21:14+0200',
            'VK_LANG' => 'LAT',
            'VK_AUTO' => 'N',
            'VK_ENCODING' => 'UTF-8',
            'VK_MAC' => 'EQNQkaI9JtHjbU+3gEVMaOw8jMdS85mx+OIZd8PlL+v+YAl3uEfuqAQiOnXe4tziUb0qhaciVveW8bPd4r98iJDVgu3kNPzVNs817hP4XFBpgz1DY8O4wqvyBM7iRbX2dPKgQgrZ0dGwQ9ixipgq4ou65GGqUxSP1/WfM40VJGdsH5z1FchajKym3gphiM03OMqxe62Ib6nsr8i3efGGfCtgOiP/7vnOWCOPnRU0gtTKGTE+Tv0PAjuYQCjPIoyAcnPzoQzuAFlv6XiDGTndekZPuz5R3rsHvYAh2C7Ln3lJUTb435mJDRTh63OveEwdFKyZjDoJ7xXSdYXiCehGeA=='
        );

        $this->getHttpRequest()->setMethod('POST');
        $this->getHttpRequest()->request->replace($postData);

        $response = $this->gateway->completePurchase($this->options)->send();

        $this->assertFalse($response->isPending());
        $this->assertTrue($response->isSuccessful());
        $this->assertFalse($response->isServerToServerRequest());
        $this->assertFalse($response->isRedirect());
        $this->assertFalse($response->isCancelled());
        $this->assertSame('abc123', $response->getTransactionReference());
        $this->assertSame('Payment was successful', $response->getMessage());
    }

    public function testPurchaseCompleteSuccessWithGET()
    {
        $getData = array(
            'VK_SERVICE' => '1111',
            'VK_VERSION' => '009',
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
            'VK_T_DATETIME' => '2020-03-13T07:21:14+0200',
            'VK_LANG' => 'LAT',
            'VK_AUTO' => 'Y',
            'VK_ENCODING' => 'UTF-8',
            'VK_MAC' => 'EQNQkaI9JtHjbU+3gEVMaOw8jMdS85mx+OIZd8PlL+v+YAl3uEfuqAQiOnXe4tziUb0qhaciVveW8bPd4r98iJDVgu3kNPzVNs817hP4XFBpgz1DY8O4wqvyBM7iRbX2dPKgQgrZ0dGwQ9ixipgq4ou65GGqUxSP1/WfM40VJGdsH5z1FchajKym3gphiM03OMqxe62Ib6nsr8i3efGGfCtgOiP/7vnOWCOPnRU0gtTKGTE+Tv0PAjuYQCjPIoyAcnPzoQzuAFlv6XiDGTndekZPuz5R3rsHvYAh2C7Ln3lJUTb435mJDRTh63OveEwdFKyZjDoJ7xXSdYXiCehGeA=='
        );

        $this->getHttpRequest()->query->replace($getData);

        $response = $this->gateway->completePurchase($this->options)->send();

        $this->assertFalse($response->isPending());
        $this->assertTrue($response->isSuccessful());
        $this->assertTrue($response->isServerToServerRequest());
        $this->assertFalse($response->isRedirect());
        $this->assertFalse($response->isCancelled());
        $this->assertSame('abc123', $response->getTransactionReference());
        $this->assertSame('Payment was successful', $response->getMessage());
    }

    public function testPurchaseCompleteFailed()
    {
        $postData = array(
            'VK_SERVICE' => '1911',
            'VK_VERSION' => '009',
            'VK_SND_ID' => 'HP',
            'VK_REC_ID' => 'REFEREND',
            'VK_STAMP' => 'abc123',
            'VK_REF' => 'abc123',
            'VK_MSG' => 'Payment for order 1231223',
            'VK_MAC' => 'n9pSpXfOproOoS7xgvTGwbFr6aj+wDIu9+Yn5yaCM9VwfOu+sdZlUUMHeXM/Zalt4dcNjQten5/g230fZ6OpRxSPfzO8BE+ioE8oCvVuMgeF6HGNv/Y8TOv/1rFO2YX4BunWUbJ2zoCD2evQZnXqUFeyPuy0D9Z8Do5LisprJjJdK9IDZUf2pmVtMHJAoDRE5cx1lsU/3a9RdZtLV1YrwDlnrST/e4mSzundamBt8ye9JnD3AGTCZCzjhng1mg4J1K2KN5uo6DA4Q6MzNyDW2d/TwEDB5VM7F7GVmZn8uRLnWtQGpO4R9RpmAG4yElykXIw4L0vDClwO2VHrkrHwrg==',
            'VK_ENCODING' => 'UTF-8',
            'VK_LANG' => 'LAT',
            'VK_AUTO' => 'N',
        );

        $this->getHttpRequest()->query->replace($postData);

        $response = $this->gateway->completePurchase($this->options)->send();

        $this->assertFalse($response->isPending());
        $this->assertFalse($response->isSuccessful());
        $this->assertFalse($response->isRedirect());
        $this->assertTrue($response->isCancelled());
        $this->assertSame('abc123', $response->getTransactionReference());
        $this->assertSame('Timeout or user canceled payment', $response->getMessage());
    }

    public function testPurchaseCompleteFailedWithForgedSignature()
    {
        $postData = array(
            'VK_SERVICE' => '1911',
            'VK_VERSION' => '009',
            'VK_SND_ID' => 'HP',
            'VK_REC_ID' => 'REFEREND',
            'VK_STAMP' => 'abc123',
            'VK_CURR' => 'EUR',
            'VK_REF' => 'abc123',
            'VK_MSG' => 'Payment for order 1231223',
            'VK_LANG' => 'LAT',
            'VK_AUTO' => 'Y',
            'VK_ENCODING' => 'UTF-8',
            'VK_MAC' => 'FORGED_SIGNATURE'
        );

        $this->getHttpRequest()->query->replace($postData);

        $this->expectException(\Omnipay\Common\Exception\InvalidRequestException::class);
        $this->expectExceptionMessage('Data is corrupt or has been changed by a third party');

        $response = $this->gateway->completePurchase($this->options)->send();
    }

    public function testPurchaseCompleteFailedWithInvalidRequest()
    {
        $postData = array(
            'some_param' => 'x',
        );

        $this->getHttpRequest()->query->replace($postData);

        $this->expectException(\Omnipay\Common\Exception\InvalidRequestException::class);
        $this->expectExceptionMessage('Unknown VK_SERVICE code');

        $response = $this->gateway->completePurchase($this->options)->send();
    }

    // test with missing VK_REF parameter
    public function testPurchaseCompleteFailedWithIncompleteRequest()
    {
        $postData = array(
            'VK_SERVICE' => '1111',
            'VK_VERSION' => '009',
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
            'VK_MSG' => 'Payment for order 1231223',
            'VK_LANG' => 'LAT',
            'VK_AUTO' => 'N',
            'VK_ENCODING' => 'UTF-8',
            'VK_MAC' => 'uHB+cjwJa7O1eCo/mwh81aAy9esSTEmExdKvWDxZrK3pn3l/Utr5Sy1vnDUzJSWGq24tBTA3saCmoVZON1FW1XRIwFyd04rhEXG2VwX+zLTzUKOEM+K98Xzs2HX8jAytjlsF2XlJYbxNM3hBej8MndvRHaBYNCl6h4Lv/y9js2z05mi2tTHKamK4w5kVOTDkV1Za0Aafx2rFoQMMqFmE+26TUcUx+Q8IvJ6vGM5+VRnCsCKzQxzN4YYftRFJo+8SHefsdhNirr10UHbkwJFNzhyuKjeEkOglCaEcq+syOhY9MDQ58AVY50vs1/q42dXicv+fTNFvu6tglSNDQJ7Ikg=='
        );

        $this->getHttpRequest()->query->replace($postData);

        $this->expectException(\Omnipay\Common\Exception\InvalidRequestException::class);
        $this->expectExceptionMessage('The VK_REF parameter is required');

        $response = $this->gateway->completePurchase($this->options)->send();
    }
}
