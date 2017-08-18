<?php

namespace Omnipay\SwedbankBanklink\Messages;

class CompleteRequest extends AbstractRequest
{

    public function getData()
    {
        //TODO: read in parameters
        $data = [];
        return $data;
    }

    /*
     * Faking sending flow
     */
    public function createResponse(array $data)
    {
        return $purchaseResponseObj = new CompleteResponse($this, $data);
    }
}