<?php

namespace Omnipay\SwedbankBanklink\Utils;

/**
 * Trait for debug logging capabilities
 * 
 * Provides optional debug logging for API requests and responses.
 * Logging is disabled by default and can be enabled via setDebugLogging().
 */
trait HasDebugLogging
{
    /**
     * Check if debug logging is enabled
     *
     * Checks request-level setting first, then gateway-level setting.
     * Defaults to false if not set anywhere.
     *
     * @return bool
     */
    public function isDebugLogging(): bool
    {
        // Check if explicitly set on the request
        if ($this->getParameter('debugLogging') !== null) {
            return (bool) $this->getParameter('debugLogging');
        }

        // Fall back to gateway setting if available
        if (isset($this->gateway) && $this->gateway && method_exists($this->gateway, 'isDebugLogging')) {
            return $this->gateway->isDebugLogging();
        }

        return false;
    }

    /**
     * Set debug logging
     *
     * Sets debug logging at the request level, overriding the gateway-level setting.
     * Typically, you should set this at the gateway level via gateway->setDebugLogging(),
     * but this allows per-request override if needed.
     *
     * @param bool $value
     * @return $this
     */
    public function setDebugLogging(bool $value): self
    {
        return $this->setParameter('debugLogging', $value);
    }

    /**
     * Log the Swedbank API request for debugging
     *
     * @param array $requestData The request data to log
     * @return void
     */
    protected function logRequest(array $requestData): void
    {
        if (!$this->isDebugLogging()) {
            return;
        }

        if (class_exists('\Illuminate\Support\Facades\Log')) {
            try {
                // Pretty-print body if it's a JSON string
                $prettyData = $requestData;
                if (isset($prettyData['body']) && is_string($prettyData['body'])) {
                    $bodyDecoded = json_decode($prettyData['body'], true);
                    if ($bodyDecoded !== null) {
                        $prettyData['body'] = $bodyDecoded;
                    }
                }
                
                \Illuminate\Support\Facades\Log::channel('payments')->debug('Swedbank V3 API Request: ' . json_encode($prettyData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            } catch (\Exception $e) {
                // Logging failed, continue anyway
            }
        }
    }

    /**
     * Log the Swedbank API response for debugging
     *
     * @param array $responseData The response data to log
     * @return void
     */
    protected function logResponse(array $responseData): void
    {
        if (!$this->isDebugLogging()) {
            return;
        }

        if (class_exists('\Illuminate\Support\Facades\Log')) {
            try {
                // Exclude body from logging if response_data is present (they're the same data)
                $prettyData = $responseData;
                
                if (isset($prettyData['body']) && isset($prettyData['response_data'])) {
                    unset($prettyData['body']);
                }
                
                \Illuminate\Support\Facades\Log::channel('payments')->debug('Swedbank V3 API Response: ' . json_encode($prettyData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            } catch (\Exception $e) {
                // Logging failed, continue anyway
            }
        }
    }
}
