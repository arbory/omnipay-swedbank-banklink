<?php

namespace Omnipay\SwedbankBanklink\Messages;

use Omnipay\Common\Message\AbstractRequest as BaseAbstractRequest;
use Omnipay\SwedbankBanklink\Utils\JwsSignature;
use Omnipay\SwedbankBanklink\Utils\HasBaseUrl;
use Omnipay\SwedbankBanklink\Utils\HasDebugLogging;

/**
 * Abstract Request for Swedbank V3 API
 * 
 * Base class for all Swedbank Payment Initiation API V3 requests.
 * Handles JWS signature generation and verification.
 * 
 * @link https://pi.swedbank.com/developer?version=public_V3
 */
abstract class AbstractRequest extends BaseAbstractRequest
{
    use HasBaseUrl;
    use HasDebugLogging;
    
    /**
     * Initialize request with gateway parameters
     */
    public function initialize(array $parameters = [])
    {
        // Ensure baseUrl is available from gateway config
        if (!isset($parameters['baseUrl']) && isset($this->gateway) && $this->gateway) {
            $gatewayBaseUrl = $this->gateway->getParameter('baseUrl');

            if (!empty($gatewayBaseUrl)) {
                $parameters['baseUrl'] = $gatewayBaseUrl;
            }
        }

        return parent::initialize($parameters);
    }

    /**
     * Get merchant ID
     *
     * @return string
     */
    public function getMerchantId(): string
    {
        return $this->getParameter('merchantId');
    }

    /**
     * Set merchant ID
     *
     * @param string $value
     * @return $this
     */
    public function setMerchantId(string $value): self
    {
        return $this->setParameter('merchantId', $value);
    }

    /**
     * Get country code
     *
     * @return string
     */
    public function getCountry(): string
    {
        return $this->getParameter('country');
    }

    /**
     * Set country code
     *
     * @param string $value
     * @return $this
     */
    public function setCountry(string $value): self
    {
        return $this->setParameter('country', strtoupper($value));
    }

    /**
     * Get private key
     *
     * @return string
     */
    public function getPrivateKey(): string
    {
        return $this->getParameter('privateKey');
    }

    /**
     * Set private key
     *
     * @param string $value
     * @return $this
     */
    public function setPrivateKey(string $value): self
    {
        return $this->setParameter('privateKey', $value);
    }

    /**
     * Get public key
     *
     * @return string
     */
    public function getPublicKey(): string
    {
        return $this->getParameter('publicKey');
    }

    /**
     * Set public key
     *
     * @param string $value
     * @return $this
     */
    public function setPublicKey(string $value): self
    {
        return $this->setParameter('publicKey', $value);
    }

    /**
     * Get bank's public key
     *
     * @return string
     */
    public function getBankPublicKey(): string
    {
        return $this->getParameter('bankPublicKey');
    }

    /**
     * Set bank's public key
     *
     * @param string $value
     * @return $this
     */
    public function setBankPublicKey(string $value): self
    {
        return $this->setParameter('bankPublicKey', $value);
    }

    /**
     * Get signing algorithm
     *
     * @return string
     */
    public function getAlgorithm(): string
    {
        return $this->getParameter('algorithm') ?? 'RS512';
    }

    /**
     * Set signing algorithm
     *
     * @param string $value
     * @return $this
     */
    public function setAlgorithm(string $value): self
    {
        return $this->setParameter('algorithm', $value);
    }

    /**
     * Get locale
     *
     * @return string
     */
    public function getLocale(): string
    {
        return $this->getParameter('locale') ?? 'en';
    }

    /**
     * Set locale
     *
     * @param string $value Locale code: en, et, lv, lt, or ru
     * @return $this
     */
    public function setLocale(string $value): self
    {
        return $this->setParameter('locale', strtolower($value));
    }

    /**
     * Get HTTP method
     *
     * @return string
     */
    public function getHttpMethod(): string
    {
        return 'POST';
    }

    /**
     * Send the request with appropriate headers and signature
     *
     * @param mixed $data The data to send
     * @return AbstractResponse
     */
    public function sendData($data): AbstractResponse
    {
        $url = $this->getEndpoint();
        $body = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        // Generate JWS signature
        $jwsSignature = JwsSignature::sign(
            $body,
            $url,
            $this->getMerchantId(),
            $this->getCountry(),
            $this->getPrivateKey(),
            $this->getAlgorithm()
        );

        // Log the request for debugging purposes
        $this->logRequest([
            'method' => $this->getHttpMethod(),
            'url' => $url,
            'body' => $body,
            'jws_signature' => $jwsSignature,
            'merchant_id' => $this->getMerchantId(),
            'country' => $this->getCountry(),
            'algorithm' => $this->getAlgorithm(),
        ]);

        // Prepare headers
        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'x-jws-signature' => $jwsSignature,
        ];

        try {
            $httpResponse = $this->httpClient->request(
                $this->getHttpMethod(),
                $url,
                $headers,
                $body
            );

            // Get response body - must be exact bytes for signature verification
            $bodyStream = $httpResponse->getBody();
            if (method_exists($bodyStream, 'rewind')) {
                $bodyStream->rewind();
            }
            $responseBody = $bodyStream->getContents();
            $responseData = json_decode($responseBody, true) ?? [];

            // Verify response signature
            $responseJws = $httpResponse->getHeader('x-jws-signature');
            if ($responseJws) {
                // getHeader() can return either string or array, ensure it's a string
                if (is_array($responseJws)) {
                    $responseJws = $responseJws[0] ?? null;
                }
                
                if ($responseJws) {
                    try {
                        $isValid = JwsSignature::verify(
                            $responseJws,
                            $responseBody,
                            $this->getBankPublicKey(),
                            120 // 120 seconds tolerance
                        );
                    } catch (\Exception $signatureEx) {
                        // Signature verification threw an exception
                        $responseData['_signature_error'] = $signatureEx->getMessage();
                        $isValid = false;
                        
                        // Log signature verification failure
                        $this->logResponse([
                            'status' => $httpResponse->getStatusCode(),
                            'body' => $responseBody,
                            'signature_valid' => false,
                            'signature_error' => $signatureEx->getMessage(),
                            'jws_signature' => substr($responseJws, 0, 100) . '...',
                            'timestamp' => date('Y-m-d H:i:s'),
                        ]);
                    }
                } else {
                    $responseData['_signature_error'] = 'No signature header found';
                    $isValid = false;
                    
                    // Log missing signature
                    $this->logResponse([
                        'status' => $httpResponse->getStatusCode(),
                        'body' => $responseBody,
                        'signature_valid' => false,
                        'signature_error' => 'No signature header found',
                        'timestamp' => date('Y-m-d H:i:s'),
                    ]);
                }

                if (!$isValid) {
                    $responseData['_signature_invalid'] = true;
                }
            }

            // Log successful response
            $this->logResponse([
                'status' => $httpResponse->getStatusCode(),
                'body' => $responseBody,
                'signature_valid' => ($isValid ?? true),
                'response_data' => $responseData,
                'timestamp' => date('Y-m-d H:i:s'),
            ]);

            return $this->createResponse($responseData, $httpResponse->getStatusCode());

        } catch (\Exception $e) {
            // Log the exception/error
            $this->logResponse([
                'error' => true,
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'exception_class' => get_class($e),
                'timestamp' => date('Y-m-d H:i:s'),
            ]);
            
            return $this->createResponse([
                'error' => $e->getMessage(),
                'status' => 'ERROR'
            ], 500);
        }
    }

    /**
     * Create response object
     *
     * @param array $data
     * @param int $statusCode
     * @return AbstractResponse
     */
    abstract protected function createResponse(array $data, int $statusCode): AbstractResponse;

    /**
     * Get the endpoint URL for this request
     *
     * @return string
     */
    abstract public function getEndpoint(): string;
}
