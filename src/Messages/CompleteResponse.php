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
        // TODO: add here CREATED and Pending state?
        if(isset($this->data['RESULT']) && $this->data['RESULT'] == 'OK'){
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
        if(isset($this->data['RESULT']) && $this->data['RESULT'] == 'TIMEOUT'){
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isPending()
    {
        //TODO: is CREATED the same as pending?
        if(isset($this->data['RESULT']) && $this->data['RESULT'] == 'PENDING'){
            return true;
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->data['RESULT'] ?? $this->data['RESULT'];
    }



}