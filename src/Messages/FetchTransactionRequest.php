<?php

namespace Omnipay\SwedbankBanklink\Messages;

use Omnipay\Common\Exception\InvalidRequestException;

/**
 * Fetch Transaction Request (Step 3)
 * 
 * Poll payment status or complete payment after return from bank
 * 
 * OpenAPI Response Schema: StatusResponseV3
 * API Endpoint: GET /public/api/v3/transactions/{id}/status
 * Documentation: https://pi.swedbank.com/developer?version=public_V3
 */
class FetchTransactionRequest extends AbstractRequest
{
    /**
     * Get HTTP method (GET for status polling)
     *
     * @return string
     */
    public function getHttpMethod(): string
    {
        return 'GET';
    }

    /**
     * Get JWS Signature body
     *
     * @return string
     */
    public function getJWSBody($data): string
    {
        return '';
    }

    /**
     * Get the data for this request
     *
     * @return array
     * @throws InvalidRequestException
     */
    public function getData(): array
    {
        $this->validate('transactionReference');

        // GET request has no body
        return [];
    }

    /**
     * Get the endpoint URL
     *
     * @return string
     */
    public function getEndpoint(): string
    {
        // V3 API format: /public/api/v3/transactions/{id}/status
        return $this->getBaseUrl() . '/public/api/v3/transactions/' .
               $this->getTransactionReference() . '/status';
    }

    /**
     * Create response object
     *
     * @param array $data
     * @param int $statusCode
     * @return FetchTransactionResponse
     */
    protected function createResponse(array $data, int $statusCode): AbstractResponse
    {
        return new FetchTransactionResponse($this, $data, $statusCode);
    }
}
