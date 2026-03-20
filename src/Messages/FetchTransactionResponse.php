<?php

namespace Omnipay\SwedbankBanklink\Messages;

/**
 * Fetch Transaction Response (Step 3)
 * 
 * Response for payment status polling.
 * Contains complete transaction details and status.
 * 
 * OpenAPI Schema: StatusResponseV3
 * Endpoint: GET /public/api/v3/transactions/{id}/status
 * 
 * V3 API Statuses:
 * - NOT_INITIATED: Transaction registered, not initiated
 * - ABANDONED: Not initiated within 1 hour
 * - INITIAL: Payment initiated by user
 * - STARTED: Payment initiation started
 * - IN_PROGRESS: Waiting for final status
 * - IN_AUTHENTICATION: Waiting for user authentication
 * - IN_CONFIRMATION: Waiting for user confirmation
 * - IN_DOUBLE_SIGNING: Requires second person to confirm
 * - EXECUTED: Successfully initiated (final, successful)
 * - SETTLED: Successfully settled (final, successful, only if settlement account with Swedbank)
 * - FAILED: Payment initiation failed (final)
 * - CANCELLED_BY_USER: Cancelled by user (final)
 * - UNKNOWN: Waiting for status change
 * - EXPIRED: No final status in expected timeframe (final)
 * 
 * @link https://pi.swedbank.com/developer?version=public_V3
 */
class FetchTransactionResponse extends AbstractResponse
{
    /**
     * Successful/completed payment statuses (V3)
     */
    private const SUCCESS_STATUSES = [
        'EXECUTED',  // Successfully initiated, not yet settled
        'SETTLED',   // Successfully settled (only if settlement account with Swedbank)
    ];

    /**
     * Pending payment statuses (V3)
     */
    private const PENDING_STATUSES = [
        'NOT_INITIATED',
        'INITIAL',
        'STARTED',
        'IN_PROGRESS',
        'IN_AUTHENTICATION',
        'IN_CONFIRMATION',
        'IN_DOUBLE_SIGNING',
        'UNKNOWN',
    ];

    /**
     * Failed/rejected payment statuses (V3)
     */
    private const FAILED_STATUSES = [
        'ABANDONED',          // Not initiated within 1 hour
        'FAILED',            // Payment initiation failed
        'CANCELLED_BY_USER', // Cancelled by user
        'EXPIRED',           // No final status in expected timeframe
    ];

    /**
     * Is the transaction successful?
     *
     * @return bool
     */
    public function isSuccessful(): bool
    {
        if (!parent::isSuccessful()) {
            return false;
        }

        $status = $this->getStatus();
        return in_array($status, self::SUCCESS_STATUSES, true);
    }

    /**
     * Is the transaction pending?
     *
     * @return bool
     */
    public function isPending(): bool
    {
        $status = $this->getStatus();
        return in_array($status, self::PENDING_STATUSES, true);
    }

    /**
     * Is the transaction cancelled?
     *
     * @return bool
     */
    public function isCancelled(): bool
    {
        return $this->getStatus() === 'CANCELLED_BY_USER';
    }

    /**
     * Has the transaction failed?
     *
     * @return bool
     */
    public function isFailed(): bool
    {
        $status = $this->getStatus();
        return in_array($status, self::FAILED_STATUSES, true);
    }

    /**
     * Get payment status
     *
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->data['status'] ?? null;
    }

    /**
     * Get payment ID (transaction reference)
     *
     * @return string|null
     */
    public function getTransactionReference(): ?string
    {
        // V3 API returns 'transactionId' field (per StatusResponseV3 schema)
        return $this->data['transactionId'] ?? null;
    }

    /**
     * Get transaction ID (same as getTransactionReference for V3)
     *
     * @return string|null
     */
    public function getTransactionId(): ?string
    {
        return $this->getTransactionReference();
    }

    /**
     * Get instructed amount
     *
     * @return string|null
     */
    public function getAmount(): ?string
    {
        // V3 API returns flat 'amount' field
        return $this->data['amount'] ?? null;
    }

    /**
     * Get currency
     *
     * @return string|null
     */
    public function getCurrency(): ?string
    {
        // V3 API returns flat 'currency' field
        return $this->data['currency'] ?? null;
    }

    /**
     * Get provider BIC (per V3 schema field 'debtorBic')
     *
     * @return string|null
     */
    public function getProvider(): ?string
    {
        return $this->data['debtorBic'] ?? null;
    }

    /**
     * Get created timestamp (ISO-8601, per V3 schema field 'createdAt')
     *
     * @return string|null
     */
    public function getCreatedAt(): ?string
    {
        return $this->data['createdAt'] ?? null;
    }

    /**
     * Get status updated timestamp (ISO-8601)
     *
     * @return string|null
     */
    public function getStatusUpdatedAt(): ?string
    {
        return $this->data['statusUpdatedAt'] ?? null;
    }

    /**
     * Get status checked timestamp (ISO-8601)
     *
     * @return string|null
     */
    public function getStatusCheckedAt(): ?string
    {
        return $this->data['statusCheckedAt'] ?? null;
    }

    /**
     * Get updated timestamp (alias for statusUpdatedAt per V3 schema)
     *
     * @return string|null
     */
    public function getUpdatedAt(): ?string
    {
        return $this->getStatusUpdatedAt();
    }

    /**
     * Get debtor name (payer name, per V3 schema field 'debtor')
     *
     * @return string|null
     */
    public function getDebtorName(): ?string
    {
        return $this->data['debtor'] ?? null;
    }

    /**
     * Get debtor account (payer account IBAN)
     *
     * @return string|null
     */
    public function getDebtorAccount(): ?string
    {
        // V3 API returns flat 'debtorAccount' field with IBAN
        return $this->data['debtorAccount'] ?? null;
    }

    /**
     * Get debtor BIC
     *
     * @return string|null
     */
    public function getDebtorBic(): ?string
    {
        return $this->data['debtorBic'] ?? null;
    }

    /**
     * Get creditor name (merchant name, per V3 schema field 'creditor')
     *
     * @return string|null
     */
    public function getCreditorName(): ?string
    {
        return $this->data['creditor'] ?? null;
    }

    /**
     * Get creditor account (merchant account IBAN)
     *
     * @return string|null
     */
    public function getCreditorAccount(): ?string
    {
        // V3 API returns flat 'creditorAccount' field with IBAN
        return $this->data['creditorAccount'] ?? null;
    }

    /**
     * Get creditor BIC
     *
     * @return string|null
     */
    public function getCreditorBic(): ?string
    {
        return $this->data['creditorBic'] ?? null;
    }

    /**
     * Get remittance information (description or reference)
     *
     * @return string|null
     */
    public function getRemittanceInformation(): ?string
    {
        // V3 API uses 'description' and 'reference' fields
        return $this->data['description'] ?? $this->data['reference'] ?? null;
    }

    /**
     * Get payment description (unstructured reference)
     *
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->data['description'] ?? null;
    }

    /**
     * Get payment reference (structured reference)
     *
     * @return string|null
     */
    public function getReference(): ?string
    {
        return $this->data['reference'] ?? null;
    }

    /**
     * Get reference type (SCOR or RF)
     *
     * @return string|null
     */
    public function getReferenceType(): ?string
    {
        return $this->data['referenceType'] ?? null;
    }

    /**
     * Get end-to-end identification (system-generated in V3, per schema field 'endToEndIdentification')
     *
     * @return string|null
     */
    public function getEndToEndId(): ?string
    {
        return $this->data['endToEndIdentification'] ?? null;
    }

    /**
     * Get payment type (instant or sepa)
     *
     * @return string|null
     */
    public function getPaymentType(): ?string
    {
        return $this->data['paymentType'] ?? null;
    }

    /**
     * Get error details (V3)
     *
     * @return string|null
     */
    public function getErrorDetails(): ?string
    {
        return $this->data['errorDetails'] ?? null;
    }

    /**
     * Get localized error labels (V3)
     *
     * @return array|null
     */
    public function getErrorLabels(): ?array
    {
        return $this->data['errorLabels'] ?? null;
    }

    /**
     * Get the latest status change timestamp (V3 uses statusUpdatedAt directly)
     *
     * @return string|null
     */
    public function getLastStatusChange(): ?string
    {
        return $this->getStatusUpdatedAt();
    }

    /**
     * Get reason for rejection/failure (V3 uses errorDetails or errorLabels)
     *
     * @return string|null
     */
    public function getRejectionReason(): ?string
    {
        // V3 provides errorDetails as primary field
        if ($this->getErrorDetails()) {
            return $this->getErrorDetails();
        }

        // Check for localized error messages (errorLabels)
        $errorLabels = $this->getErrorLabels();
        if ($errorLabels && is_array($errorLabels)) {
            // Return first available language
            return reset($errorLabels) ?: null;
        }

        return null;
    }

    /**
     * Get full payment details as array (V3 structure)
     *
     * @return array
     */
    public function getPaymentDetails(): array
    {
        return [
            'transactionId' => $this->getTransactionReference(),
            'status' => $this->getStatus(),
            'amount' => $this->getAmount(),
            'currency' => $this->getCurrency(),
            'paymentType' => $this->getPaymentType(),
            'debtorBic' => $this->getDebtorBic(),
            'debtor' => $this->getDebtorName(),
            'debtorAccount' => $this->getDebtorAccount(),
            'creditorBic' => $this->getCreditorBic(),
            'creditor' => $this->getCreditorName(),
            'creditorAccount' => $this->getCreditorAccount(),
            'reference' => $this->getReference(),
            'referenceType' => $this->getReferenceType(),
            'description' => $this->getDescription(),
            'endToEndIdentification' => $this->getEndToEndId(),
            'createdAt' => $this->getCreatedAt(),
            'statusUpdatedAt' => $this->getStatusUpdatedAt(),
            'statusCheckedAt' => $this->getStatusCheckedAt(),
            'errorDetails' => $this->getErrorDetails(),
            'errorLabels' => $this->getErrorLabels(),
        ];
    }
}
