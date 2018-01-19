<?php

namespace Omnipay\SwedbankBanklink\Messages;

use Omnipay\Common\Message\RedirectResponseInterface;

class PurchaseResponse extends AbstractResponse  implements RedirectResponseInterface
{

    public function isSuccessful()
    {
        return false; //needs redirect
    }

    // Redirect is processed from merchants HTML form by auto-submitting it to gateway
    // Use this flag if you want to render custom redirect form
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
        /** @var PurchaseRequest $request */
        $request = $this->getRequest();
        return $request->getGatewayUrl();
    }

}