<?php

namespace Omnipay\SwedbankBanklink;

use Omnipay\Common\AbstractGateway;

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
            'banklinkUrl'           => 'https://ib.swedbank.lv/banklink/',
            'merchantId'            => '', //VK_SND_ID
            'returnUrl'             => '',
            'certificatePath'       => '',
            'certificatePassword'   => '',
        );
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setCertificatePassword($value)
    {
        return $this->setParameter('certificatePassword', $value);
    }

    /**
     * @return string
     */
    public function getCertificatePassword()
    {
        return $this->getParameter('certificatePassword');
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
    public function getClientIP()
    {
        return $this->getParameter('clientIP');
    }

    /**
     * @param $value
     * @return $this
     */
    public function setClientIP($value)
    {
        return $this->setParameter('clientIP', $value);
    }

    /**
     * Execute SMS transaction
     * @param array $options
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function purchase(array $options = [])
    {
        return $this->createRequest(PurchaseRequest::class, $options);
    }

    /**
     * Request transaction result
     * @param array $options
     * @return \Omnipay\Common\Message\AbstractRequest
     */
    public function completePurchase(array $options = [])
    {
        return $this->createRequest(CompleteRequest::class, $options);
    }

}