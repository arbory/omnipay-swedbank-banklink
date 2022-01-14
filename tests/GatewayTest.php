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

        // regenerate new bank mac with: self::generateControlCode($data, $encoding, 'tests/Fixtures/key.pem', '')
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
            'VK_REF' => 'abc123',
            'VK_MSG' => 'purchase description',
            'VK_MAC' => 'cD0FQXbvtlQyj0EAwro5NQdrG1SS65zNhsML7m7ca7gy4KI+8Css9vGir7PFg8+FwV/3qtqncvQfH0GwrA1eP1ozCO6YhJta8WnmREqREfWNPbUcTxWi6hpZkbdYWeXZ+iETYlhI8cL6/ob9jUsqLzihDiea/FXdnien7FpE8cjDAGde1hjeQH32+iHp9/WwfYZRE+p5lKB/I9zhQnGsmlA34rty13BL6sD2u85I0Q73p/CUJl7ZiYMnzBqUE8ERdyp467Q+IGPh8RwRvileYt447SG6XX8PtrQ1xoMWpPOxYYnO4AfTA3STzyERv5ejslNtfAX09l+1nDRjx/9k2g==',
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
            'VK_REF' => 'abc123',
            'VK_MSG' => 'purchase description',
            'VK_MAC' => 'KkmmXu2XJ8wL9ONSxCTHPE7zhKQYob7Nvh4Ivq/TQn1Hq+4rjULOLYgj/lYKF+EUVvFmNGoFZyJ0uGSDat1vcxrdUYBFCTocJhOHN9o1SNvfzlU0IfcVw+NqnNUm3CYZykJmcLIeqkPjjoEy2OKTz6oWf9Dvf/9q179xIHzBDl3cZg0HvRvKmL9LVjWKKOlv8AfenGzOZ47crxw1OVnDSO270WdwBywkVfEiFDDDvFe+kGmvrF/zFftvR+RbIf15YR9S2np7vxYOakok4JOZrXmxNIJLgdH/BROjyIzlKbVBQsZ6AkLEHsAc+VFFqipgIWCJF1XFnurTDixOKgo23A==',
            'VK_RETURN' => 'http://localhost:8080/omnipay/banklink/',
            'VK_LANG' => 'LAT',
            'VK_ENCODING' => 'UTF-8',
            'VK_CANCEL' => 'http://localhost:8080/omnipay/banklink/',
            'VK_DATETIME' => '2017-03-03T09:06:41+0000',
        ), $response->getData());

        $this->assertEquals($response->getData(), $response->getRedirectData());
    }

    public function testPurchaseCompleteSuccess()
    {
        $postData = array(
            'VK_SERVICE' => '1111',
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
            'VK_T_DATETIME' => '2020-03-13T07:21:14+0200',
            'VK_LANG' => 'LAT',
            'VK_AUTO' => 'N',
            'VK_ENCODING' => 'UTF-8',
            'VK_MAC' => 'hlSDn/KlUjpeWxUQt8zOB+1HzWp8hhUroscUM/HoGRZAFSuwLLMpsfVysAKEw8nXG96QQxdO0YKqlETwOhDAnnKqJNciEmMoRAxmsdxvWsOZpbEjDDcvETs1s5dxpCZl4JnmT4/3zV/TTegBzULaCxJRAF0Pe6ZdmmUVnrMH4W3kDbyCfldrh6ghJ/pXMFfSbmGd/Z4478KmS1sX3jESQS6JRjbEeJsxyqfuGAbqhL3v99Ynxl/7dq6PUiGvYhEoXgnpW4rxOpkllz3ISBr3FvDBrjFJxHDbePQHVCkxxajopCZ4uJy4UeEEpYNUb33TJ6JoSo4CxQzFCLX3yinekQ=='
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
            'VK_T_DATETIME' => '2020-03-13T07:21:14+0200',
            'VK_LANG' => 'LAT',
            'VK_AUTO' => 'Y',
            'VK_ENCODING' => 'UTF-8',
            'VK_MAC' => 'hlSDn/KlUjpeWxUQt8zOB+1HzWp8hhUroscUM/HoGRZAFSuwLLMpsfVysAKEw8nXG96QQxdO0YKqlETwOhDAnnKqJNciEmMoRAxmsdxvWsOZpbEjDDcvETs1s5dxpCZl4JnmT4/3zV/TTegBzULaCxJRAF0Pe6ZdmmUVnrMH4W3kDbyCfldrh6ghJ/pXMFfSbmGd/Z4478KmS1sX3jESQS6JRjbEeJsxyqfuGAbqhL3v99Ynxl/7dq6PUiGvYhEoXgnpW4rxOpkllz3ISBr3FvDBrjFJxHDbePQHVCkxxajopCZ4uJy4UeEEpYNUb33TJ6JoSo4CxQzFCLX3yinekQ=='
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
            'VK_VERSION' => '008',
            'VK_SND_ID' => 'HP',
            'VK_REC_ID' => 'REFEREND',
            'VK_STAMP' => 'abc123',
            'VK_CURR' => 'EUR',
            'VK_REF' => 'abc123',
            'VK_MSG' => 'Payment for order 1231223',
            'VK_T_DATETIME' => '2020-03-13T07:21:14+0200',
            'VK_LANG' => 'LAT',
            'VK_AUTO' => 'N',
            'VK_ENCODING' => 'UTF-8',
            'VK_MAC' => 'pOdJTcWaX5GyL/CuhsbnBY0hCN7ImaFSVdzEN4jTQmmaSq1oRr+r6kdS+1KktE7jfXfMi0vsnSPVVF7YhAVPQvXqJoZYVocA9m1KFX/B5i/m6xYqSRDUFzomfas1M1Er0QAHf0EOA6W+Z/z0zqJ9IdDg1Jj9S+dM32orA2JAGtYM3Se/O22v+agP42dpt2iVcPRi3PeLIObImIOSn0FeObMYEGQNrb8s5RBBFwsChGblOa1lcPCvkFHuqWgioimp6rHtlewPYB03tNyUkmyOeTPFC3WcJRqbE9Z7egTkuFawgqUybhuDDmX5v4B1vMK76+6VBVNxZsTH+kVV7ArXeA=='
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
