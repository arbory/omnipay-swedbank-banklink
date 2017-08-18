<?php

namespace Omnipay\SwedbankBanklink\Messages;

use Omnipay\Common\Message\RedirectResponseInterface;

class PurchaseResponse extends AbstractResponse  implements RedirectResponseInterface
{

    public function getTransactionReference()
    {
       return null;
    }

    public function isSuccessful()
    {
        return false; //needs redirect
    }

    // Redirect is processed from merchants HTML form by auto-submitting it to gateway
    // https://github.com/thephpleague/omnipay/issues/306
    public function isTransparentRedirect(){
        return true;
    }

    public function isRedirect()
    {
        return true;
    }

    public function getRedirectMethod()
    {
        return 'POST';
    }

    public function getRedirectData(){
        return $this->getData();
    }

    public function getRedirectUrl()
    {
       return $this->getGatewayUrl();
    }

}