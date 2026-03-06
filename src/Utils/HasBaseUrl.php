<?php

namespace Omnipay\SwedbankBanklink\Utils;

/**
 * Trait for resolving base URL from multiple sources
 *
 * Provides consistent baseUrl resolution logic across Gateway and Requests.
 * Resolution order:
 *   1. Parameter (explicit setting)
 *   2. Config (from laravel-omnipay config)
 *   3. Environment variable (SWEDBANK_GATEWAY_URL)
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

        if (empty($baseUrl)) {
            throw new \RuntimeException(
                'baseUrl is not configured. Please set the baseUrl parameter, '
                . 'config value, or SWEDBANK_GATEWAY_URL environment variable.'
            );
        }

        return rtrim($baseUrl, '/'); // Remove trailing slash if present
    }
}
