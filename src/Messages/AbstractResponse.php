<?php

namespace Omnipay\SwedbankBanklink\Messages;

use Omnipay\Common\Message\AbstractResponse as CommonAbstractResponse;

abstract class AbstractResponse extends CommonAbstractResponse
{
    private $returnUrl = null;
    private $gatewayUrl = null;

    public function setReturnUrl($returnUrl){
        $this->returnUrl = $returnUrl;
    }

    public function getReturnUrl()
    {
        return $this->returnUrl;
    }

    public function setGatewayUrl($value)
    {
        $this->gatewayUrl = $value;
    }

    public function getGatewayUrl()
    {
        return $this->gatewayUrl;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return null;
    }

}