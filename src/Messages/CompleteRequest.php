<?php

namespace Omnipay\SwedbankBanklink\Messages;

use Omnipay\Common\Exception\InvalidResponseException;
use Omnipay\SwedbankBanklink\Utils\Pizza;
use Symfony\Component\HttpFoundation\ParameterBag;

class CompleteRequest extends AbstractRequest
{
    /**
     * array with required response keys, boolean shows if field used for control code calculation
     * @var array
     */
    protected $errorResponse = [
        'VK_SERVICE' => true,
        'VK_VERSION' => true,
        'VK_SND_ID' => true,
        'VK_REC_ID' => true,
        'VK_STAMP' => true,
        'VK_REF' => true,
        'VK_MSG' => true,
        'VK_MAC' => false,
        'VK_LANG' => false,
        'VK_AUTO' => false,
        'VK_ENCODING' => false
    ];

    /**
     * array with required response keys, boolean shows if field used for control code calculation
     * @var array
     */
    protected $successResponse = [
        'VK_SERVICE' => true,
        'VK_VERSION' => true,
        'VK_SND_ID' => true,
        'VK_REC_ID' => true,
        'VK_STAMP' => true,
        'VK_T_NO' => true,
        'VK_AMOUNT' => true,
        'VK_CURR' => true,
        'VK_REC_ACC' => true,
        'VK_REC_NAME' => true,
        'VK_SND_ACC' => true,
        'VK_SND_NAME' => true,
        'VK_REF' => true,
        'VK_MSG' => true,
        'VK_T_DATE' => true,
        'VK_MAC' => false,
        'VK_LANG' => false,
        'VK_AUTO' => false,
        'VK_ENCODING' => false
    ];

    public function getData()
    {
        // Read data from  HTTP req. object
        $data = $this->getResponseData();
        return $data;
    }

    /*
     * Faking sending flow
     */
    public function createResponse(array $data)
    {
        // Read data from request object
        return $purchaseResponseObj = new CompleteResponse($this, $data);
    }

    /**
     * @return array
     * @throws InvalidResponseException
     */
    public function getResponseData()
    {
        /** @var ParameterBag $queryObj */
        $queryObj = $this->httpRequest->query;

        $result = $this->isValidResponse($errMessages);

        if($result) {
            return $queryObj->all();
        }else{
            throw new InvalidResponseException(join(';', $errMessages));
        }
    }

    /**
     * @return bool
     */
    protected function isValidResponse(&$errMessages)
    {
        $errMessages = [];
        $queryObj = $this->httpRequest->query;

        if (in_array($queryObj->get('VK_SERVICE'), ['1101', '1901'])) {
            $responseKeys = $queryObj->get('VK_SERVICE') == '1101' ? $this->successResponse : $this->errorResponse;

            // Get keys that are required for control code generation
            $controlCodeKeys = array_filter($responseKeys, function($val){ return $val; });

            // Get control code required fields with values
            $controlCodeFields = array_intersect_key( $queryObj->all(), $controlCodeKeys );

            if(count($controlCodeFields) != count ($controlCodeKeys)){
                $errMessages[] = 'Required fields are missing in response';
            }

            //If you are testing requests by spoofing manually bank response, don't forget to url encode VK_MAC value
            //https://stackoverflow.com/questions/5628738/strange-base64-encode-decode-problem
            //$test = Pizza::test($controlCodeFields, $this->getCertificatePath(), $this->getEncoding());

            if(!Pizza::isValidControlCode($controlCodeFields, $queryObj->get('VK_MAC'), $this->getCertificatePath(), $this->getEncoding())){
                $errMessages[] = 'Invalid control code';
            }else{
                return true;
            }
        } else {
            $errMessages[] = 'Unsupported service ID:' . intval($queryObj->get('VK_SERVICE'));
        }

        return false;
    }
}