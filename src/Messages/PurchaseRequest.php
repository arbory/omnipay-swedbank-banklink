<?php

namespace Omnipay\SwedbankBanklink\Messages;

use Omnipay\SwedbankBanklink\Utils\Pizza;

class PurchaseRequest extends AbstractRequest
{

    /**
     * @return array
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     */
    private function getEncodedData()
    {
        $data = [
            'VK_SERVICE'    => '1002', // Service code
            'VK_VERSION'    => '008', // Protocol version
            'VK_SND_ID'     => $this->getMerchantId(),
            'VK_STAMP'      => $this->getTransactionId(),  // Max 20 length
            'VK_AMOUNT'     => $this->getAmount(), // Decimal with point
            'VK_CURR'       => $this->getCurrency(), // ISO 4217 format (LVL/EUR, etc.)
            'VK_REF'        => $this->getTransactionId(),  // Max 20 length
            'VK_MSG'        => $this->getDescription(), // Max 300 length
        ];
        return $data;
    }

    /**
     * @return array
     */
    private function getDecodedData(){
        $data = [
            'VK_MAC'        => base64_encode($this->generateControlCode($this->getEncodedData())), // MAC - Control code / signature
            'VK_RETURN'     => $this->getReturnUrl(), // Transaction (1101, 1901) response url, 150 max length
            'VK_LANG'       => $this->getLanguage(), // Communication language (LAT, ENG RUS), no format standard?
            'VK_ENCODING'   => $this->getEncoding() // ISO-8850-13 (def) or UTF-8
        ];

        return $data;
    }

    /**
     * @param $data
     * @return string
     */
    private function generateControlCode($data)
    {
        return Pizza::generateControlCode($data, $this->getEncoding(), $this->getCertificatePath());
    }

    /**
     * @param $value
     */
    public function setMerchantId($value)
    {
        $this->setParameter('merchantId', $value);
    }

    /**
     * @return mixed
     */
    public function getMerchantId()
    {
        return $this->getParameter('merchantId');
    }

    /**
     * Glue together encoded and raw data
     * @return array
     */
    public function getData()
    {
        $data = $this->getEncodedData() + $this->getDecodedData();
        return $data;
    }

    /**
     * @param       $httpResponse
     * @param array $data
     * @return PurchaseResponse
     */
    public function createResponse(array $data)
    {
        return $purchaseResponseObj = new PurchaseResponse($this, $data);
    }
}