<?php

namespace Omnipay\SwedbankBanklink\Messages;

use  Omnipay\Common\Message\AbstractRequest as CommonAbstractRequest;

abstract class AbstractRequest extends CommonAbstractRequest
{
    /**
     * @param $data array
     * @param $httpClient
     * @return AbstractResponse
     */
    abstract protected function createResponse(array $data);

    /**
     * @return mixed
     */
    public function getControlCode()
    {
        return $this->getParameter('controlCode');
    }

    /**
     * @param mixed $controlCode
     */
    public function setControlCode($value)
    {
        return $this->setParameter('controlCode', $value);
    }



    /**
     * @return mixed
     */
    public function getEncoding()
    {
        return $this->getParameter('encoding');
    }

    /**
     * @param mixed $encoding
     */
    public function setEncoding($value)
    {
        return $this->setParameter('encoding', $value);
    }

    /**
     * @return mixed
     */
    public function getReturnUrl()
    {
        return $this->getParameter('returnUrl');
    }

    /**
     * @param mixed $returnUrl
     */
    public function setReturnUrl($value)
    {
        return $this->setParameter('returnUrl', $value);
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setCertificatePath($value)
    {
        return $this->setParameter('certificatePath', $value);
    }

    /**
     * @return string
     */
    public function getCertificatePath()
    {
        return $this->getParameter('certificatePath');
    }

    /**
     * @return mixed
     */
    public function getLanguage()
    {
        return $this->getParameter('language');
    }

    /**
     * @param $value
     * @return CommonAbstractRequest
     */
    public function setLanguage($value)
    {
        return $this->setParameter('language', $value);
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setGatewayUrl($value)
    {
        return $this->setParameter('gatewayUrl', $value);
    }

    /**
     * @return string
     */
    public function getGatewayUrl()
    {
        return $this->getParameter('gatewayUrl');
    }

    /**
     * @param mixed $data
     * @return \Guzzle\Http\Message\Response
     * @throws \Omnipay\Common\Exception\InvalidRequestException
     */
    public function sendData($data)
    {
        $this->validate('certificatePath');
        // Create fake response flow, so that user can be redirected
        /** @var AbstractResponse $purchaseResponseObj */
        $purchaseResponseObj = $this->createResponse($data);
        $purchaseResponseObj->setReturnUrl($this->getReturnUrl());
        $purchaseResponseObj->setGatewayUrl($this->getGatewayUrl());
        return $purchaseResponseObj;
    }

}