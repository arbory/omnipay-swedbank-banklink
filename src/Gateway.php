<?php

namespace Omnipay\SwedbankBanklink;

use Omnipay\Common\AbstractGateway;
use Omnipay\SwedbankBanklink\Messages\PurchaseRequest;
use Omnipay\SwedbankBanklink\Messages\CompleteRequest;

/**
 * Class Gateway
 *
 * @package Omnipay\SwedbankBanklink
 */
class Gateway extends AbstractGateway
{
    /**
     * @return string
     */
    public function getName()
    {
        return 'Swedbank Banlink';
    }

    /**
     * @return array
     */
    public function getDefaultParameters()
    {
        return array(
            'gatewayUrl'           => 'https://www.swedbank.lv/banklink/',
            'merchantId'            => '', //VK_SND_ID
            'returnUrl'             => '',
            'privateCertificatePath' => '',
            'publicCertificatePath' => '',
            'privateCertificatePassphrase' => null,

            //Global parameters for requests will be set via gateway
            'language'              => 'LAT',
        );
    }


    /**
     * @param array $options
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function purchase(array $options = [])
    {
        return $this->createRequest(PurchaseRequest::class, $options);
    }

    /**
     * Complete transaction
     * @param array $options
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function completePurchase(array $options = [])
    {
        return $this->createRequest(CompleteRequest::class, $options);
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
     * @param string $value
     * @return $this
     */
    public function setMerchantId($value)
    {
        return $this->setParameter('merchantId', $value);
    }

    /**
     * @return string
     */
    public function getMerchantId()
    {
        return $this->getParameter('merchantId');
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setReturnUrl($value)
    {
        return $this->setParameter('returnUrl', $value);
    }

    /**
     * @return string
     */
    public function getReturnUrl()
    {
        return $this->getParameter('returnUrl');
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
     * @param string $value
     * @return $this
     */
    public function setPrivateCertificatePassphrase($value)
    {
        return $this->setParameter('privateCertificatePassphrase', $value);
    }

    /**
     * @return string
     */
    public function getPrivateCertificatePassphrase()
    {
        return $this->getParameter('privateCertificatePassphrase');
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
     * @return mixed
     */
    public function getLanguage()
    {
        return $this->getParameter('language');
    }

    /**
     * @param $value
     */
    public function setLanguage($value)
    {
        return $this->setParameter('language', $value);
    }
}
