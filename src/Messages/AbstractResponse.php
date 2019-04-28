<?php

namespace Omnipay\SwedbankBanklink\Messages;

use Omnipay\Common\Message\AbstractResponse as CommonAbstractResponse;

abstract class AbstractResponse extends CommonAbstractResponse
{
    public function getTransactionReference()
    {
        $data = $this->getData();
        return $data['VK_REF'] ?? $data['VK_REF'];
    }
}
