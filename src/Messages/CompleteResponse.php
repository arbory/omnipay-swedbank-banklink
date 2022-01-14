<?php

namespace Omnipay\SwedbankBanklink\Messages;

use Symfony\Component\HttpFoundation\ParameterBag;
use Omnipay\SwedbankBanklink\Utils\Pizza;

class CompleteResponse extends AbstractResponse
{
    /**
     * @return bool
     */
    public function isSuccessful(): bool
    {
        if ($this->data['VK_SERVICE'] == '1111') {
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
    public function isCancelled(): bool
    {
        return $this->data['VK_SERVICE'] == '1911';
    }

    public function getMessage()
    {
        if ($this->data['VK_SERVICE'] == '1911') {
            return 'Timeout or user canceled payment';
        }
        return 'Payment was successful';
    }

    /**
     * @return bool
     */
    public function isServerToServerRequest(): bool
    {
        return ($this->data['VK_AUTO'] ?? null) == 'Y';
    }
}
