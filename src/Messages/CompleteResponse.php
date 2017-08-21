<?php

namespace Omnipay\SwedbankBanklink\Messages;

class CompleteResponse extends AbstractResponse
{
    /**
     * Use only RESULT data to determine transactions state
     * Other fields are for debugging and logging!
     * This is from Payeezy IP admin manual
     * @return bool
     */
    public function isSuccessful()
    {
        return $this->data['VK_SERVICE'] == '1101';
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

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->data['VK_SERVICE'];
    }
}