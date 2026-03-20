<?php

namespace Omnipay\SwedbankBanklink\Messages;

/**
 * Fetch Providers Request (Step 1)
 *
 * Get list of available payment providers/banks
 * 
 * OpenAPI Response Schema: ServiceViewImplV2V3 (array)
 * API Endpoint: GET /public/api/v3/agreement/providers
 * Documentation: https://pi.swedbank.com/developer?version=public_V3
 */
class FetchProvidersRequest extends AbstractRequest
{
    /**
     * Get HTTP method (GET for fetching providers)
     *
     * @return string
     */
    public function getHttpMethod(): string
    {
        return 'GET';
    }

    /**
     * Get the data for this request
     *
     * @return array
     */
    public function getData(): array
    {
        // This endpoint accepts an empty request body
        return [];
    }

    /**
     * Get the endpoint URL
     *
     * @return string
     */
    public function getEndpoint(): string
    {
        return $this->getBaseUrl() . '/public/api/v3/agreement/providers';
    }

    /**
     * Create response object
     *
     * @param array $data
     * @param int $statusCode
     * @return FetchProvidersResponse
     */
    protected function createResponse(array $data, int $statusCode): AbstractResponse
    {
        return new FetchProvidersResponse($this, $data, $statusCode);
    }
}
