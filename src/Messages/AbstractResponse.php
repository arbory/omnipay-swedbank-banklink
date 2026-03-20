<?php

namespace Omnipay\SwedbankBanklink\Messages;

use Omnipay\Common\Message\AbstractResponse as BaseAbstractResponse;
use Omnipay\Common\Message\RequestInterface;
use Omnipay\SwedbankBanklink\Utils\ApiResponseParser;

/**
 * Abstract Response for Swedbank V3 API
 * 
 * Base class for all Swedbank Payment Initiation API V3 responses.
 * 
 * @link https://pi.swedbank.com/developer?version=public_V3
 */
abstract class AbstractResponse extends BaseAbstractResponse
{
    protected const HTTP_STATUS_OK_START = 200;
    protected const HTTP_STATUS_REDIRECTION_START = 300;
    /**
     * @var int
     */
    protected $statusCode;

    /**
     * Constructor
     *
     * @param RequestInterface $request
     * @param mixed $data
     * @param int $statusCode
     */
    public function __construct(RequestInterface $request, $data, int $statusCode = 200)
    {
        parent::__construct($request, $data);
        $this->statusCode = $statusCode;
    }

    /**
     * Is the response successful?
     *
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this->statusCode >= self::HTTP_STATUS_OK_START
            && $this->statusCode < self::HTTP_STATUS_REDIRECTION_START
            && !isset($this->data['error'])
            && !isset($this->data['_signature_invalid']);
    }

    /**
     * Get the error message from the response
     *
     * @return string|null
     */
    public function getMessage(): ?string
    {
        return ApiResponseParser::getMessage($this->data);
    }

    /**
     * Get the error code from the response
     *
     * @return string|null
     */
    public function getCode(): ?string
    {
        return isset($this->data['code']) ? (string) $this->data['code'] : null;
    }

    /**
     * Get HTTP status code
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get transaction reference (payment ID from Swedbank)
     * V3 API returns 'id' field for payment creation responses
     *
     * @return string|null
     */
    public function getTransactionReference(): ?string
    {
        return $this->data['id'] ?? null;
    }

    /**
     * Get transaction ID (merchant's order ID)
     *
     * @return string|null
     */
    public function getTransactionId(): ?string
    {
        return $this->data['merchantTransactionId'] ?? null;
    }
}
