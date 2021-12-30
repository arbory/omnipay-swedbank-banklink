<?php

namespace Omnipay\SwedbankBanklink\Utils;

use Omnipay\Tests\TestCase;

class PizzaTest extends TestCase
{
    public function testGenerateControlCode()
    {
        $data = array('SOME' => 'data');
        $encoding = 'UTF-8';
        $privateCertPath = 'tests/Fixtures/key.pem';
        $privateCertificatePassphrase = null;

        $expectedControlCode = 'aFUgw6CJu6vjY2uSEWzzBa+8iu4AozflO7+HPwmFJsxg9AFnL9xuoIKFiSL4CsRhVY+3OuLt4j6jBEAQnG3iH+NGMi6b77llj/MyeLKL5iieaW5hfpbjZkwsqeZ1WYJo/xFQQTTp4ipBbr7xXVXeHs7q9P+ViaI/RpXP5KY9OlTb+jDUU+Rewbhpjn7LdBbAw62cJy4eTZMldrhlRHJ0nX+LTh3jpa3/h5jevQF1A2+sl3Z/j0jHDDd0YZBubGdh7+DRL69lL5zg7OiiI7iJXZ/PutYNBdw7Ko5aDsgp2HDYvW7CdSpRts1aFki/2VAkLqD9mhEjmarPCq65RGpXLA==';
        $this->assertSame($expectedControlCode, Pizza::generateControlCode($data, $encoding, $privateCertPath, $privateCertificatePassphrase));
    }

    public function testCreateHash()
    {
        $data = array('SOME' => 'data', 'INT_VALUE' => 123, 'LONG_TEXT' => 'asd1239ekjhsdkashdashdksajd');
        $encoding = 'UTF-8';

        $expectedHash = '004data003123027asd1239ekjhsdkashdashdksajd';

        $this->assertSame($expectedHash, Pizza::createHash($data, $encoding));
    }

    public function testIsValidControlCode()
    {
        $data = array('SOME' => 'data');
        $encoding = 'UTF-8';
        $publicCertPath = 'tests/Fixtures/key.pub';
        $signatureEncoded = 'UJNqocqzeglyVh7uy0bWXyCh3mUsOMWHokOekxDPpmsLRkCHxPNzlygtgresIBfpAhI6siAfIHvTMgDp6infHseJXJwLvgC5UDGINa6ruH3Oc9sQiU0pPoKnnCvT/0YHfiljI6X0cQfeLu2gk1ezK5CsNALrBOoT7uJ56t+Gcb38ioXjn1RgH7lcv0eX4Jj1tJLoiLngepOMcSIjfzXhBSdZ0O9H5DAJdo7SitP8HReuXWKeug9gXiAzkbup5JlMU2Y6jazf0hYWoMtP0IRUuL9q49y2qhd+NQWo+HXqT97E5Vj7THk5LApBYbJ5ZHq2bq4Kc7UFfmxd4+JTqRANuA==';

        $this->assertTrue(Pizza::isValidControlCode($data, $signatureEncoded, $publicCertPath, $encoding));
    }

    public function testIsValidControlCodeWithInvalidPublicCertificate()
    {
        $data = array('SOME' => 'data');
        $encoding = 'UTF-8';
        $publicCertPath = 'tests/Fixtures/key.pem';
        $signatureEncoded = 'UJNqocqzeglyVh7uy0bWXyCh3mUsOMWHokOekxDPpmsLRkCHxPNzlygtgresIBfpAhI6siAfIHvTMgDp6infHseJXJwLvgC5UDGINa6ruH3Oc9sQiU0pPoKnnCvT/0YHfiljI6X0cQfeLu2gk1ezK5CsNALrBOoT7uJ56t+Gcb38ioXjn1RgH7lcv0eX4Jj1tJLoiLngepOMcSIjfzXhBSdZ0O9H5DAJdo7SitP8HReuXWKeug9gXiAzkbup5JlMU2Y6jazf0hYWoMtP0IRUuL9q49y2qhd+NQWo+HXqT97E5Vj7THk5LApBYbJ5ZHq2bq4Kc7UFfmxd4+JTqRANuA==';

        $this->expectException(\RuntimeException::class);
        Pizza::isValidControlCode($data, $signatureEncoded, $publicCertPath, $encoding);
    }

    public function testIsValidControlCodeWithInvalidSignature()
    {
        $data = array('SOME' => 'lasdskd');
        $encoding = 'UTF-8';
        $publicCertPath = 'tests/Fixtures/key.pub';
        $signatureEncoded = 'UJNqocqzeglyVh7uy0bWXyCh3mUsOMWHokOekxDPpmsLRkCHxPNzlygtgresIBfpAhI6siAfIHvTMgDp6infHseJXJwLvgC5UDGINa6ruH3Oc9sQiU0pPoKnnCvT/0YHfiljI6X0cQfeLu2gk1ezK5CsNALrBOoT7uJ56t+Gcb38ioXjn1RgH7lcv0eX4Jj1tJLoiLngepOMcSIjfzXhBSdZ0O9H5DAJdo7SitP8HReuXWKeug9gXiAzkbup5JlMU2Y6jazf0hYWoMtP0IRUuL9q49y2qhd+NQWo+HXqT97E5Vj7THk5LApBYbJ5ZHq2bq4Kc7UFfmxd4+JTqRANuA==';

        $this->assertFalse(Pizza::isValidControlCode($data, $signatureEncoded, $publicCertPath, $encoding));
    }
}
