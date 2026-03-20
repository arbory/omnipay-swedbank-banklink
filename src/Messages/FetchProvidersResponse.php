<?php

namespace Omnipay\SwedbankBanklink\Messages;

/**
 * Fetch Providers Response (Step 1)
 * 
 * Response containing list of available payment providers (banks).
 * 
 * OpenAPI Schema: ServiceViewImplV2V3 (array)
 * Endpoint: GET /public/api/v3/agreement/providers
 * 
 * V3 API Response Structure:
 * Each provider contains:
 * - country: Country code (LT, EE, LV)
 * - bic: Bank identifier (e.g., HABALT22)
 * - names: Object with shortNames and longNames (localized)
 * - urls: Object with logo and payment URLs
 * 
 * @link https://pi.swedbank.com/developer?version=public_V3
 */
class FetchProvidersResponse extends AbstractResponse
{
    /**
     * Is the response successful?
     *
     * @return bool
     */
    public function isSuccessful(): bool
    {
        // V3 API returns array directly, not wrapped in 'providers'
        return parent::isSuccessful() && is_array($this->data);
    }

    /**
     * Get list of available providers
     *
     * @return array
     */
    public function getProviders(): array
    {
        // V3 API returns array directly
        if (is_array($this->data) && !isset($this->data['providers'])) {
            return $this->data;
        }
        
        return $this->data['providers'] ?? [];
    }

    /**
     * Get provider by BIC code (V3 uses 'bic', not 'id')
     *
     * @param string $bic Provider BIC code (e.g., HABAEE2X)
     * @return array|null
     */
    public function getProvider(string $bic): ?array
    {
        $providers = $this->getProviders();
        
        foreach ($providers as $provider) {

            // Check for 'bic' field first (V3), then fallback to 'id' for backwards compatibility
            if (($provider['bic'] ?? $provider['id'] ?? null) === $bic) {
                return $provider;
            }
        }
        
        return null;
    }

    /**
     * Alias for getProvider (for backwards compatibility)
     *
     * @param string $providerId
     * @return array|null
     */
    public function getProviderById(string $providerId): ?array
    {
        return $this->getProvider($providerId);
    }

    /**
     * Get providers filtered by country
     *
     * @param string $country Country code (LT, EE, LV)
     * @return array
     */
    public function getProvidersByCountry(string $country): array
    {
        return array_filter($this->getProviders(), function ($provider) use ($country) {
            return isset($provider['country']) && $provider['country'] === strtoupper($country);
        });
    }

    /**
     * Get enabled providers only (V3 doesn't have 'enabled' field, all returned providers are available)
     *
     * @return array
     */
    public function getEnabledProviders(): array
    {
        // V3 API only returns available providers, so all are "enabled"
        return $this->getProviders();
    }

    /**
     * Check if a specific provider is available (V3)
     *
     * @param string $bic Provider BIC code
     * @return bool
     */
    public function isProviderEnabled(string $bic): bool
    {
        return $this->getProvider($bic) !== null;
    }

    /**
     * Alias for isProviderEnabled
     *
     * @param string $bic
     * @return bool
     */
    public function isProviderAvailable(string $bic): bool
    {
        return $this->isProviderEnabled($bic);
    }

    /**
     * Get provider names mapped by BIC (V3 structure)
     *
     * @param string $locale Locale for name (default: 'en')
     * @param bool $useLongName Use long name instead of short name
     * @return array [bic => name]
     */
    public function getProviderNames(string $locale = 'en', bool $useLongName = false): array
    {
        $names = [];
        $nameType = $useLongName ? 'longNames' : 'shortNames';

        foreach ($this->getProviders() as $provider) {
            $bic = $provider['bic'] ?? null;
            if (!$bic) continue;

            $names[$bic] = $provider['names'][$nameType][$locale]
                ?? $provider['names'][$nameType]['en']
                ?? $provider['name']
                ?? $bic;
        }

        return $names;
    }
}
