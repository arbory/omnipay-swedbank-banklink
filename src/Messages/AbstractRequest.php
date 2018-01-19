<?php

namespace Omnipay\SwedbankBanklink\Messages;

use  Omnipay\Common\Message\AbstractRequest as CommonAbstractRequest;

abstract class AbstractRequest extends CommonAbstractRequest
{
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
    public function setPublicCertificatePath($value)
    {
        return $this->setParameter('publicCertificatePath', $value);
    }

    /**
     * @return string
     */
    public function getPublicCertificatePath()
    {
        return $this->getParameter('publicCertificatePath');
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setPrivateCertificatePath($value)
    {
        return $this->setParameter('privateCertificatePath', $value);
    }

    /**
     * @return string
     */
    public function getPrivateCertificatePath()
    {
        return $this->getParameter('privateCertificatePath');
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
}