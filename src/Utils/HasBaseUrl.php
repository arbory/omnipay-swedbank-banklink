<?php

namespace Omnipay\SwedbankBanklink\Utils;

/**
 * Trait for resolving base URL from multiple sources
 *
 * Provides consistent baseUrl resolution logic across Gateway and Requests.    
 * Resolution order:
 *   1. Parameter (explicit setting)
 *   2. Config (from laravel-omnipay config)
 *   3. Environment variables (SWEDBANK_GATEWAY_URL_SANDBOX/PROD based on testMode, or legacy SWEDBANK_GATEWAY_URL)
 */
trait HasBaseUrl
{
    /**
     * Get base URL from parameter, config, or environment
     *
     * @return string
     * @throws \RuntimeException
     */
    public function getBaseUrl(): string
    {
        $baseUrl = $this->getParameter('baseUrl');

        // If not explicitly set, try config helper
        if (empty($baseUrl) && function_exists('config')) {
            $baseUrl = config('laravel-omnipay.gateways.swedbank-banklink.options.baseUrl');
        }

        // If still not set, try environment variables
        if (empty($baseUrl)) {
            // Try sandbox/prod URLs based on test mode
            $testMode = $this->getParameter('testMode') ?? getenv('SWEDBANK_TEST_MODE');
            
            if ($testMode === true || $testMode === 'true' || $testMode === '1') {
                $baseUrl = getenv('SWEDBANK_GATEWAY_URL_SANDBOX');
            } else {
                $baseUrl = getenv('SWEDBANK_GATEWAY_URL_PROD');
            }
            
            // Fall back to legacy SWEDBANK_GATEWAY_URL if neither is set
            if (empty($baseUrl)) {
                $baseUrl = getenv('SWEDBANK_GATEWAY_URL');
            }
        }

        if (empty($baseUrl)) {
            throw new \RuntimeException(
                'baseUrl is not configured. Please set the baseUrl parameter, ' 
                . 'config value, or SWEDBANK_GATEWAY_URL_SANDBOX/PROD (or legacy SWEDBANK_GATEWAY_URL) environment variables.' 
            );
        }

        return rtrim($baseUrl, '/'); // Remove trailing slash if present        
    }
}
