<?php

namespace Omnipay\SwedbankBanklink\Messages;

use Omnipay\Common\Message\RedirectResponseInterface;

/**
 * Purchase Response (Step 2)
 * 
 * Response from payment initiation request.
 * Contains redirect URL and transaction ID.
 * 
 * OpenAPI Schema: PaymentCreationResponse
 * Endpoint: POST /public/api/v3/transactions/providers/{bic}
 * 
 * API Response Structure (V3):
 * - id: Transaction identifier
 * - urls.redirect: URL to redirect user to bank
 * - urls.status: URL for status polling
 * 
 * @link https://pi.swedbank.com/developer?version=public_V3
 */
class PurchaseResponse extends AbstractResponse implements RedirectResponseInterface
{
    /**
     * Is the response successful?
     * For payment initiation, we expect a redirect URL
     *
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return false; // Payment is not complete until user authorizes at bank
    }

    /**
     * Does the response require a redirect?
     *
     * @return bool
     */
    public function isRedirect(): bool
    {
        return parent::isSuccessful() && !empty($this->getRedirectUrl());
    }

    /**
     * Get the redirect URL
     *
     * @return string|null
     */
    public function getRedirectUrl(): ?string
    {
        // V3 API returns nested structure: urls.redirect
        return $this->data['urls']['redirect'] ?? null;
    }

    /**
     * Get redirect method (always GET for Swedbank)
     *
     * @return string
     */
    public function getRedirectMethod(): string
    {
        return 'GET';
    }

    /**
     * Get redirect data (none needed for GET redirect)
     *
     * @return array
     */
    public function getRedirectData(): array
    {
        return [];
    }

    /**
     * Get payment ID from Swedbank (transaction reference)
     *
     * @return string|null
     */
    public function getTransactionReference(): ?string
    {
        // V3 API returns 'id' field
        return $this->data['id'] ?? null;
    }

    /**
     * Get status polling URL
     *
     * @return string|null
     */
    public function getStatusUrl(): ?string
    {
        return $this->data['urls']['status'] ?? null;
    }
}
