<?php

namespace Omnipay\SwedbankBanklink\Utils;

/**
 * Resolves the provider BIC code from a payment type string.
 *
 * When the consuming application has a BankLink model it can register
 * a custom resolver via setResolver(). Without one, the raw payment_type
 * value is returned directly (expected to already be a BIC), falling back
 * to the Swedbank default BIC when no payment_type is available.
 */
class ProviderResolver
{
    public const DEFAULT_BIC = 'HABALV22';

    /** @var callable|null */
    private static $resolver = null;

    /**
     * Register a custom resolver callable.
     * The callable receives a payment_type string and must return a BIC string or null.
     *
     * @param callable $resolver
     */
    public static function setResolver(callable $resolver): void
    {
        self::$resolver = $resolver;
    }

    /**
     * Resolve a BIC from the given payment_type value.
     * Returns the DEFAULT_BIC when payment_type is empty and no resolver is set.
     *
     * @param string|null $paymentType
     * @return string
     */
    public static function resolve(?string $paymentType): string
    {
        if (empty($paymentType)) {
            return self::DEFAULT_BIC;
        }

        if (self::$resolver !== null) {
            $resolved = (self::$resolver)($paymentType);
            if ($resolved) {
                return $resolved;
            }
        }

        return $paymentType;
    }
}

