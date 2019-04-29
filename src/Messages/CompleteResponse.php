<?php

namespace Omnipay\SwedbankBanklink\Messages;

use Symfony\Component\HttpFoundation\ParameterBag;
use Omnipay\SwedbankBanklink\Utils\Pizza;

class CompleteResponse extends AbstractResponse
{
    /**
     * @return bool
     */
    public function isSuccessful()
    {
        if ($this->data['VK_SERVICE'] == '1101') {
            return true;
        }
        return false;
    }

    /**
     * Checks if user has canceled transaction
     * Only way user can cancel transaction is via timeout, there are no other ways
     *
     * @return bool
     */
    public function isCancelled()
    {
        return $this->data['VK_SERVICE'] == '1901';
    }

    public function getMessage()
    {
        if ($this->data['VK_SERVICE'] == '1901') {
            return "Timeout or user canceled payment";
        }
        return "";
    }

    public function getData()
    {
        return $this->data;
    }
}
