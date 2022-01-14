<?php

namespace Omnipay\SwedbankBanklink\Messages;

use Datetime;
use Omnipay\SwedbankBanklink\Utils\Pizza;

class PurchaseRequest extends AbstractRequest
{
    protected const ENCODING_UTF_8 = 'UTF-8';

    /**
     * @return array
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     */
    private function getEncodedData()
    {
        $data = [
            'VK_SERVICE'    => '1012', // Service code
            'VK_VERSION'    => '009', // Protocol version
            'VK_SND_ID'     => $this->getMerchantId(),
            'VK_STAMP'      => $this->getTransactionReference(),  // Max 20 length
            'VK_AMOUNT'     => $this->getAmount(), // Decimal with point
            'VK_CURR'       => $this->getCurrency(), // ISO 4217 format (LVL/EUR, etc.)
            'VK_REF'        => $this->getTransactionReference(),  // Max 20 length
            'VK_MSG'        => $this->getDescription(), // Max 300 length
            'VK_RETURN'     => $this->getReturnUrl(), // Transaction (1101, 1901) response url, 150 max length
            'VK_CANCEL'     => $this->getReturnUrl(), // Transaction (1101, 1901) response url, 150 max length
            'VK_DATETIME'   => $this->getDateTime(), // Max 24 length
        ];
        return $data;
    }

    /**
     * @return array
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     */
    private function getDecodedData()
    {
        $data = [
            'VK_MAC'        => $this->generateControlCode($this->getEncodedData()), // MAC - Control code / signature
            'VK_LANG'       => $this->getLanguage(), // Communication language (LAT, ENG RUS), no format standard?
            'VK_ENCODING'   => self::ENCODING_UTF_8 // UTF-8
        ];

        return $data;
    }

    /**
     * @param $data
     * @return string
     */
    private function generateControlCode($data)
    {
        return Pizza::generateControlCode(
            $data,
            self::ENCODING_UTF_8,
            $this->getPrivateCertificatePath(),
            $this->getPrivateCertificatePassphrase()
        );
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
     * @return string
     */
    public function setDateTime($value)
    {
        $this->setParameter('dateTime', $value);
    }

    /**
     * @param $value
     */
    public function getDateTime()
    {
        $dateTime = is_null($this->getParameter('dateTime')) ? new DateTime() : $this->getParameter('dateTime');
        return $dateTime->format(DateTime::ISO8601);
    }

    /**
     * Glue together encoded and raw data
     * @return array|mixed
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     */
    public function getData()
    {
        return array_merge($this->getEncodedData(), $this->getDecodedData());
    }

    /**
     * @param mixed $data
     * @return \Omnipay\Common\Message\ResponseInterface|PurchaseResponse
     */
    public function sendData($data)
    {
        // Create fake response flow, so that user can be redirected
        /** @var AbstractResponse $purchaseResponseObj */
        return new PurchaseResponse($this, $data);
    }
}
