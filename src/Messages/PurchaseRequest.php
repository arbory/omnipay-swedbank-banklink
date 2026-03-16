<?php

namespace Omnipay\SwedbankBanklink\Messages;

use Omnipay\Common\Exception\InvalidRequestException;

/**
 * Purchase Request (Step 2)
 * 
 * Initiate a payment with Swedbank
 * 
 * OpenAPI Schema: PaymentCreationRequestV3
 * API Endpoint: POST /public/api/v3/transactions/providers/{bic}
 * Documentation: https://pi.swedbank.com/developer?version=public_V3
 */
class PurchaseRequest extends AbstractRequest
{
    /**
     * Get provider ID
     *
     * @return string
     */
    public function getProvider(): string
    {
        return $this->getParameter('provider');
    }

    /**
     * Set provider ID (BIC)
     *
     * @param string|null $value Provider BIC code (e.g., HABAEE2X)
     * @return $this
     */
    public function setProvider(?string $value): self
    {
        return $this->setParameter('provider', $value);
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
     * Get notification URL
     *
     * @return string|null
     */
    public function getNotificationUrl(): ?string
    {
        return $this->getParameter('notificationUrl');
    }

    /**
     * Set notification URL
     *
     * @param string $value
     * @return $this
     */
    public function setNotificationUrl(string $value): self
    {
        return $this->setParameter('notificationUrl', $value);
    }

    /**
     * Get payment reference (structured reference number per ISO11649)
     *
     * @return string|null
     */
    public function getReference(): ?string
    {
        return $this->getParameter('reference');
    }

    /**
     * Set payment reference (structured reference number per ISO11649)
     *
     * @param string $value
     * @return $this
     */
    public function setReference(string $value): self
    {
        return $this->setParameter('reference', $value);
    }

    /**
     * Validate the request
     *
     * @throws InvalidRequestException
     */
    public function validate(...$args): void
    {
        parent::validate(
            'provider',
            'amount',
            'currency',
            'returnUrl',
            'notificationUrl',
            'locale',
            ...$args,
        );

        // Validate amount format (spec: positive number with up to 2 fractional digits)
        $amount = $this->getAmount();
        if (!preg_match('/^\d+(\.\d{1,2})?$/', $amount) || (float)$amount <= 0) {
            throw new InvalidRequestException('Amount must be a positive number with up to 2 decimal places (e.g., 10.00)');
        }

        // Validate currency - V3 only supports EUR per spec
        $currency = $this->getCurrency();
        if ($currency !== 'EUR') {
            throw new InvalidRequestException('Invalid currency. Only EUR is supported in V3 API');
        }

        // Validate locale per spec: "en", "et", "lv", "lt", "ru"
        $locale = $this->getLocale();
        if (!in_array($locale, ['en', 'et', 'lv', 'lt', 'ru'], true)) {
            throw new InvalidRequestException('Invalid locale. Supported: en, et, lv, lt, ru');
        }

        // Validate BIC code (provider) is not empty
        $provider = $this->getProvider();
        if (empty($provider)) {
            throw new InvalidRequestException('Provider BIC code is required');
        }

        // Validate URLs are HTTPS per spec
        $redirectUrl = $this->getReturnUrl();
        if (!filter_var($redirectUrl, FILTER_VALIDATE_URL)) {
            throw new InvalidRequestException('Redirect URL is not a valid URL format');
        }
        if (!preg_match('/^https:\/\//i', $redirectUrl)) {
            throw new InvalidRequestException('Redirect URL must use HTTPS protocol');
        }
        
        // Validate redirectUrl max length (spec: 2048)
        if (strlen($redirectUrl) > 2048) {
            throw new InvalidRequestException('Redirect URL exceeds maximum length of 2048 characters');
        }
        
        $notificationUrl = $this->getNotificationUrl();
        if (!filter_var($notificationUrl, FILTER_VALIDATE_URL)) {
            throw new InvalidRequestException('Notification URL is not a valid URL format');
        }
        if (!preg_match('/^https:\/\//i', $notificationUrl)) {
            throw new InvalidRequestException('Notification URL must use HTTPS protocol');
        }
        
        // Validate notificationUrl max length (spec: 2048)
        if (strlen($notificationUrl) > 2048) {
            throw new InvalidRequestException('Notification URL exceeds maximum length of 2048 characters');
        }
        
        // Validate description and reference per spec
        $description = $this->getDescription();
        $reference = $this->getReference();
        
        // Validate description max length (spec: 140)
        if ($description && strlen($description) > 140) {
            throw new InvalidRequestException('Description exceeds maximum length of 140 characters');
        }
        
        // Validate reference format if provided
        // Per spec, reference must be in SCOR (7-3-1) or RF (ISO11649) format
        if ($reference) {
            $referenceStr = (string)$reference;
            // Check for RF format (must start with "RF" and match mod97)
            if (strpos($referenceStr, 'RF') === 0) {
                if (!$this->validateRFReference($referenceStr)) {
                    throw new InvalidRequestException('Invalid RF reference number format (must be ISO11649 compliant)');
                }
            }
            // Reference max length per spec: 25 characters
            if (strlen($referenceStr) > 25) {
                throw new InvalidRequestException('Reference exceeds maximum length of 25 characters');
            }
        }

        // Validate description/reference mutual exclusivity per spec
        // Per DescriptionOrReferenceNumberPresent rule:
        // - Estonia (domestic): both can be present
        // - Latvia/Lithuania: either description or reference, but not both
        // - Cross-border: either description or reference, but not both
        $country = strtoupper($this->getCountry());
        $isEstonia = ($country === 'EE');

        // For non-Estonia payments: enforce either/or rule
        if (!$isEstonia && $description && $reference) {
            throw new InvalidRequestException('For cross-border or Latvia/Lithuania payments, provide either description or reference, but not both');
        }

        // For cross-border payments (country may be different from agreement country)
        // At minimum, one of description or reference should be present
        // Note: This is enforced by the API itself, but we could validate here
    }

    /**
     * Validate RF reference number (ISO11649 format)
     * Must start with "RF" followed by 2 check digits and alphanumeric
     *
     * @param string $reference
     * @return bool
     */
    private function validateRFReference(string $reference): bool
    {
        // RF reference must be: RF + 2 check digits + alphanumeric (min 3-25 chars total)
        if (!preg_match('/^RF\d{2}[A-Z0-9]{1,23}$/i', $reference)) {
            return false;
        }
        
        // Validate mod97 checksum
        // Move first 4 chars to end, replace letters with digits (A=10, B=11, etc.)
        $rearranged = substr($reference, 4) . substr($reference, 0, 4);
        $numeric = '';
        
        for ($i = 0; $i < strlen($rearranged); $i++) {
            $char = $rearranged[$i];
            if (is_numeric($char)) {
                $numeric .= $char;
            } else {
                // Convert letter to number (A=10, B=11, ..., Z=35)
                $numeric .= (ord(strtoupper($char)) - ord('A') + 10);
            }
        }
        
        // Check if mod97 equals 1
        return bcmod($numeric, '97') === '1';
    }

    /**
     * Get the data for this request
     *
     * @return array
     * @throws InvalidRequestException
     */
    public function getData(): array
    {
        $this->validate();

        // V3 API structure - reference: https://pi.swedbank.com/developer/api-docs/public_V3
        $data = [
            'amount' => $this->getAmount(),
            'currency' => $this->getCurrency(),
            'redirectUrl' => $this->getReturnUrl(),
            'notificationUrl' => $this->getNotificationUrl(),
            'locale' => $this->getLocale(),
        ];

        // Add optional description (unstructured reference)
        if ($this->getDescription()) {
            $data['description'] = $this->getDescription();
        }

        // Add optional reference (structured reference number)
        if ($this->getReference()) {
            $data['reference'] = $this->getReference();
        }

        // Note: In V3, either description or reference should be present (not both for cross-border)
        // endToEndIdentification is now generated by the system in V3

        return $data;
    }

    /**
     * Get the endpoint URL
     *
     * @return string
     */
    public function getEndpoint(): string
    {
        // V3 API format: /public/api/v3/transactions/providers/{bic}
        return $this->getBaseUrl() . '/public/api/v3/transactions/providers/' . $this->getProvider();
    }

    /**
     * Create response object
     *
     * @param array $data
     * @param int $statusCode
     * @return PurchaseResponse
     */
    protected function createResponse(array $data, int $statusCode): AbstractResponse
    {
        return new PurchaseResponse($this, $data, $statusCode);
    }
}
