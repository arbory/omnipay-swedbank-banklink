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
        $this->assertTrue($response->isTransparentRedirect());
        $this->assertEquals('POST', $response->getRedirectMethod());
        $this->assertEquals('https://www.swedbank.lv/banklink/', $response->getRedirectUrl());

        $this->assertEquals(array(
            'VK_SERVICE' => 1012,
            'VK_VERSION' => '009',
            'VK_SND_ID' => '1',
            'VK_STAMP' => 'abc123',
            'VK_AMOUNT' => '10.00',
            'VK_CURR' => 'EUR',
            'VK_REF' => 'abc123',
            'VK_MSG' => 'purchase description',
            'VK_MAC' => 'VGtxFaXYXIZe9xkyxiwEQG+PjwuyFr4uXcfubKMlp8y1mEH+gAiPz9M+TGZFWUQoIBCKu/mkKB7i89OYjlQTo8GWDQtD2ovMobuhU8eeIfYsEY9oSXc3cxp0QePFMu3g8XAAweZf28jostVDdv/Q5gF82tSflTCu8rkvA7ymjNql/iF7GwwKNnTWUbJwLMRlu8XsMWry0oChKPbIeBKdO97Zxr53AG8c7gRAKmsFzKTRtIoMrJpXlVpZ7kZcGTU7Rzy/Zs6oACLhN7Vn0xDqSP+KWEGXlT7Z7DcIPQ3Y5Ghy8Ytje+khrkpM1yC3AzM66RsllNVc4JA3uI1sfxN/oA==',
            'VK_RETURN' => 'http://localhost:8080/omnipay/banklink/',
            'VK_LANG' => 'LAT',
            'VK_ENCODING' => 'UTF-8',
        ), $response->getData());

        $this->assertEquals($response->getData(), $response->getRedirectData());
    }

    public function testPurchaseSuccessWithPassphrasedPrivateKey()
    {
        $options = $this->options;
        $options['privateCertificatePath'] = 'tests/Fixtures/key_with_passphrase.pem';
        $options['privateCertificatePassphrase'] = 'foobar';

        $response = $this->gateway->purchase($options)->send();

        $this->assertInstanceOf('\Omnipay\SwedbankBanklink\Messages\PurchaseResponse', $response);
        $this->assertFalse($response->isPending());
        $this->assertFalse($response->isSuccessful());
        $this->assertTrue($response->isRedirect());
        $this->assertTrue($response->isTransparentRedirect());
        $this->assertEquals('POST', $response->getRedirectMethod());
        $this->assertEquals('https://www.swedbank.lv/banklink/', $response->getRedirectUrl());

        $this->assertEquals(array(
            'VK_SERVICE' => 1012,
            'VK_VERSION' => '009',
            'VK_SND_ID' => '1',
            'VK_STAMP' => 'abc123',
            'VK_AMOUNT' => '10.00',
            'VK_CURR' => 'EUR',
            'VK_REF' => 'abc123',
            'VK_MSG' => 'purchase description',
            'VK_MAC' => 'ftWhubDLjYssINn4FYFtKGM35WApxIPTUBf3TxA6qHzBX6bMWR60zrU1nDyxlAEZzWeIjt+1dROMcBKdxd0D29NdGfzjOaO6zuioQyjucIIK3ks5Y0E7ebx31BKoqMqgW751ko9m+yI1k338fPCQ8A9ax+1Yev5+biPwfC/wFuzeJXoAuyDBLibMLw3D3i8ZCk0eyqlKUm6wlyxDUWKs3LH0TLRTLh+/eNVILckVWuxE0gpBgfntI/vsHpK9KCaa+X5uPh9PJ6GmV2wWnTEJYlI1pZqZ9H03fUcCoMnTg85q6uVkqkw3p60oG5e8zI4vbJ03Jr0hft+cK8PWZEcq5g==',
            'VK_RETURN' => 'http://localhost:8080/omnipay/banklink/',
            'VK_LANG' => 'LAT',
            'VK_ENCODING' => 'UTF-8',
        ), $response->getData());

        $this->assertEquals($response->getData(), $response->getRedirectData());
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
            'VK_AUTO' => 'Y',
            'VK_ENCODING' => 'UTF-8',
            'VK_MAC' => 'uHB+cjwJa7O1eCo/mwh81aAy9esSTEmExdKvWDxZrK3pn3l/Utr5Sy1vnDUzJSWGq24tBTA3saCmoVZON1FW1XRIwFyd04rhEXG2VwX+zLTzUKOEM+K98Xzs2HX8jAytjlsF2XlJYbxNM3hBej8MndvRHaBYNCl6h4Lv/y9js2z05mi2tTHKamK4w5kVOTDkV1Za0Aafx2rFoQMMqFmE+26TUcUx+Q8IvJ6vGM5+VRnCsCKzQxzN4YYftRFJo+8SHefsdhNirr10UHbkwJFNzhyuKjeEkOglCaEcq+syOhY9MDQ58AVY50vs1/q42dXicv+fTNFvu6tglSNDQJ7Ikg=='
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
        $this->assertTrue($response->isCancelled());
        $this->assertSame('abc123', $response->getTransactionReference());
        $this->assertSame('Timeout or user canceled payment', $response->getMessage());
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
            'VK_AUTO' => 'Y',
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

    // test with missing VK_REF parameter
    public function testPurchaseCompleteWithMissingFields()
    {
        // missing VK_T_NO here
        $postData = array(
            'VK_SERVICE' => '1101',
            'VK_VERSION' => '008',
            'VK_SND_ID' => 'HP',
            'VK_REC_ID' => 'REFEREND',
            'VK_STAMP' => 'abc123',
            'VK_AMOUNT' => '10.00',
            'VK_CURR' => 'EUR',
            'VK_REC_ACC' => 'XXXXXXXXXX',
            'VK_REC_NAME' => 'Shop',
            'VK_SND_ACC' => 'XXXXXXXXXXXX',
            'VK_SND_NAME' => 'John Mayer',
            'VK_MSG' => 'Payment for order 1231223',
            'VK_T_DATE' => '10.03.2019',
            'VK_LANG' => 'LAT',
            'VK_AUTO' => 'N',
            'VK_ENCODING' => 'UTF-8',
            'VK_MAC' => 'uHB+cjwJa7O1eCo/mwh81aAy9esSTEmExdKvWDxZrK3pn3l/Utr5Sy1vnDUzJSWGq24tBTA3saCmoVZON1FW1XRIwFyd04rhEXG2VwX+zLTzUKOEM+K98Xzs2HX8jAytjlsF2XlJYbxNM3hBej8MndvRHaBYNCl6h4Lv/y9js2z05mi2tTHKamK4w5kVOTDkV1Za0Aafx2rFoQMMqFmE+26TUcUx+Q8IvJ6vGM5+VRnCsCKzQxzN4YYftRFJo+8SHefsdhNirr10UHbkwJFNzhyuKjeEkOglCaEcq+syOhY9MDQ58AVY50vs1/q42dXicv+fTNFvu6tglSNDQJ7Ikg=='
        );

        $this->getHttpRequest()->query->replace($postData);

        $this->expectException(\Omnipay\Common\Exception\InvalidRequestException::class);
        $this->expectExceptionMessage('Data is corrupt or has been changed by a third party');

        $response = $this->gateway->completePurchase($this->options)->send();
    }

    // test with missing VK_REF parameter
    public function testPurchaseCompleteFailedWithIncompleteRequest()
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
            'VK_MSG' => 'Payment for order 1231223',
            'VK_T_DATE' => '10.03.2019',
            'VK_LANG' => 'LAT',
            'VK_AUTO' => 'N',
            'VK_ENCODING' => 'UTF-8',
            'VK_MAC' => 'uHB+cjwJa7O1eCo/mwh81aAy9esSTEmExdKvWDxZrK3pn3l/Utr5Sy1vnDUzJSWGq24tBTA3saCmoVZON1FW1XRIwFyd04rhEXG2VwX+zLTzUKOEM+K98Xzs2HX8jAytjlsF2XlJYbxNM3hBej8MndvRHaBYNCl6h4Lv/y9js2z05mi2tTHKamK4w5kVOTDkV1Za0Aafx2rFoQMMqFmE+26TUcUx+Q8IvJ6vGM5+VRnCsCKzQxzN4YYftRFJo+8SHefsdhNirr10UHbkwJFNzhyuKjeEkOglCaEcq+syOhY9MDQ58AVY50vs1/q42dXicv+fTNFvu6tglSNDQJ7Ikg=='
        );

        $this->getHttpRequest()->query->replace($postData);

        $this->expectException(\Omnipay\Common\Exception\InvalidRequestException::class);
        $this->expectExceptionMessage('Data is corrupt or has been changed by a third party');

        $response = $this->gateway->completePurchase($this->options)->send();
    }
}
