<?php

namespace Omnipay\SwedbankBanklink\Messages;

use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\Exception\InvalidResponseException;
use Omnipay\SwedbankBanklink\Utils\Pizza;
use Symfony\Component\HttpFoundation\ParameterBag;

class CompleteRequest extends AbstractRequest
{
    protected const ENCODING_UTF_8 = 'UTF-8';

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
        'VK_ENCODING' => false,
        'VK_LANG' => false,
        'VK_AUTO' => false,
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
        'VK_T_DATETIME' => true,
        'VK_MAC' => false,
        'VK_ENCODING' => false,
        'VK_LANG' => false,
        'VK_AUTO' => false
    ];

    public function getData()
    {
        if ($this->httpRequest->getMethod() == 'POST') {
            return $this->httpRequest->request->all();
        } else {
            return $this->httpRequest->query->all();
        }
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
     * @param mixed $data
     * @return \Omnipay\Common\Message\ResponseInterface|AbstractResponse|CompleteResponse
     * @throws InvalidRequestException
     */
    public function sendData($data)
    {
        //Validate response data before we process further
        $this->validateResponseParameters();

        // Create fake response flow
        /** @var CompleteResponse $purchaseResponseObj */
        $response = $this->createResponse($data);
        return $response;
    }

    protected function validateResponseParameters()
    {
        $response = $this->getData();
        if (!isset($response['VK_SERVICE']) || !in_array($response['VK_SERVICE'], ['1111', '1911'])) {
            throw new InvalidRequestException('Unknown VK_SERVICE code');
        }

        $responseFields = $response['VK_SERVICE'] == '1111' ? $this->successResponse : $this->errorResponse;

        //check for missing fields, will throw exc. on missing fields
        foreach ($responseFields as $fieldName => $usedInHash) {
            if (! isset($response[$fieldName])) {
                throw new InvalidRequestException("The $fieldName parameter is required");
            }
        }

        //verify data corruption
        $this->validateIntegrity($responseFields);
    }

    /**
     * @return bool
     */
    protected function validateIntegrity(array $responseFields)
    {
        $responseData = new ParameterBag($this->getData());

        // Get keys that are required for control code generation
        $controlCodeKeys = array_filter($responseFields, function ($val) {
            return $val;
        });

        // Get control code required fields with values
        $controlCodeFields = array_intersect_key($responseData->all(), $controlCodeKeys);

        if (!Pizza::isValidControlCode(
            $controlCodeFields,
            $responseData->get('VK_MAC'),
            $this->getPublicCertificatePath(),
            self::ENCODING_UTF_8
        )) {
            throw new InvalidRequestException('Data is corrupt or has been changed by a third party');
        }
    }

    /**
     * @param $value
     */
    public function setMerchantId($value)
    {
        $this->setParameter('merchantId', $value);
    }

    /**
     * @return mixed
     */
    public function getMerchantId()
    {
        return $this->getParameter('merchantId');
    }
}
