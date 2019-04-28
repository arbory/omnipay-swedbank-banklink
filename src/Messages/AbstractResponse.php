<?php

namespace Omnipay\SwedbankBanklink\Messages;

use Omnipay\Common\Message\AbstractResponse as CommonAbstractResponse;

abstract class AbstractResponse extends CommonAbstractResponse
{
    /**
     * @return string
     */
    public function getMessage()
    {
        //TODO: return error message
        return null;
    }

    public function getTransactionReference()
    {
        $data = $this->getData();
        return $data['VK_REF'] ?? $data['VK_REF'];
    }
}
