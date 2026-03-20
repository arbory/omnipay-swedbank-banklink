<?php

namespace Omnipay\SwedbankBanklink\Utils;

class ApiResponseParser
{
    /**
     * Parse a message from various API response formats.
     */
    public static function getMessage(array $data): ?string
    {
        // 1. Check for Signature Failures (Security)
        if (isset($data['_signature_invalid'])) {
            $error = $data['_signature_error'] ?? '';
            return 'Invalid response signature from bank' . ($error ? ": $error" : '');
        }

        // 2. Direct Keys (Highest Priority)
        foreach (['error', 'message'] as $key) {
            if (isset($data[$key]) && is_string($data[$key])) {
                return $data[$key];
            }
        }

        // 3. Nested Formats (V3 and Standard Arrays)
        $messages = array_merge(
            self::extractV3Messages($data['errorMessages'] ?? []),
            self::extractStandardMessages($data['errors'] ?? [])
        );

        return $messages ? implode('; ', array_unique($messages)) : null;
    }

    /**
     * Handles V3 nested format: errorMessages.general and errorMessages.fields
     */
    private static function extractV3Messages(array $errorMessages): array
    {
        $collected = [];

        // General Errors
        foreach ($errorMessages['general'] ?? [] as $error) {
            if (isset($error['message'])) {
                $collected[] = $error['message'];
            }
        }

        // Field Errors
        foreach ($errorMessages['fields'] ?? [] as $error) {
            if (isset($error['field'], $error['message'])) {
                $collected[] = "{$error['field']}: {$error['message']}";
            } elseif (isset($error['message'])) {
                $collected[] = $error['message'];
            }
        }

        return $collected;
    }

    /**
     * Handles standard formats: errors array with message keys or simple string messages
     */
    private static function extractStandardMessages(array $errors): array
    {
        $collected = [];
        foreach ($errors as $error) {
            if (is_array($error) && isset($error['message'])) {
                $collected[] = $error['message'];
            } elseif (is_string($error)) {
                $collected[] = $error;
            }
        }
        return $collected;
    }
}