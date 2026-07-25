<?php

/**
 * Base helpers for converting friendly PHP arrays into Russian Post API payload arrays.
 *
 * @package     WT Otpravkapochtaru
 * @version     3.0.0
 * @author      Sergey Tolkachyov
 * @copyright   Copyright (c) 2026 Sergey Tolkachyov, WebTolk. All rights reserved.
 * @license     GNU/GPL 3.0
 * @since       0.1.0
 */

namespace Webtolk\Otpravkapochtaru\Entity;

defined('_JEXEC') or die;

use Webtolk\Otpravkapochtaru\Exception\ValidationException;

abstract class AbstractEntity
{
    /**
     * Export the entity as an API-ready associative array.
     *
     * @return  array<string, mixed>
     *
     * @since   3.0.0
     */
    abstract public function toArray(): array;

    /**
     * Normalize associative payload keys recursively to the kebab-case names used by Russian Post.
     *
     * Numeric list keys are preserved; nested associative arrays are normalized in the same pass.
     *
     * @param   array<string|int, mixed>  $data  Payload data to normalize.
     *
     * @return  array<string|int, mixed>
     *
     * @since   3.0.0
     */
    final protected static function normalizePayloadKeys(array $data): array
    {
        $normalized = [];

        foreach ($data as $key => $value) {
            if (is_int($key)) {
                $normalized[$key] = self::normalizeValue($value);

                continue;
            }

            $normalized[self::normalizeKey((string) $key)] = self::normalizeValue($value);
        }

        return $normalized;
    }

    /**
     * Remove null values recursively before sending payloads to the API.
     *
     * Empty associative child arrays are dropped, while list structure is preserved.
     *
     * @param   array<string|int, mixed>  $data  Payload data to filter.
     *
     * @return  array<string|int, mixed>
     *
     * @since   3.0.0
     */
    final protected static function filterNullValues(array $data): array
    {
        $filtered = [];

        foreach ($data as $key => $value) {
            if ($value === null) {
                continue;
            }

            if (is_array($value)) {
                if (array_is_list($value)) {
                    $filtered[$key] = array_map(
                        static fn (mixed $item): mixed => is_array($item) ? self::filterNullValues($item) : $item,
                        $value
                    );

                    continue;
                }

                $value = self::filterNullValues($value);

                if ($value === []) {
                    continue;
                }
            }

            $filtered[$key] = $value;
        }

        return $filtered;
    }

    /**
     * Assert that a required payload value is present and return it for inline use.
     *
     * @param   mixed   $value    Value to validate.
     * @param   string  $message  Exception message used when the value is empty.
     *
     * @return  mixed
     *
     * @since   3.0.0
     *
     * @throws  ValidationException
     */
    final protected static function requireNonEmpty(mixed $value, string $message): mixed
    {
        if ($value === null || $value === '' || $value === []) {
            throw new ValidationException($message);
        }

        return $value;
    }

    /**
     * Normalize a scalar or nested array value while preserving list indexes.
     *
     * @param   mixed  $value  Value to normalize.
     *
     * @return  mixed
     *
     * @since   3.0.0
     */
    private static function normalizeValue(mixed $value): mixed
    {
        if (!is_array($value)) {
            return $value;
        }

        if (array_is_list($value)) {
            return array_map(
                static fn (mixed $item): mixed => is_array($item) ? self::normalizePayloadKeys($item) : $item,
                $value
            );
        }

        return self::normalizePayloadKeys($value);
    }

    /**
     * Convert `camelCase` and `snake_case` input keys to Russian Post kebab-case keys.
     *
     * @param   string  $key  Payload key to normalize.
     *
     * @return  string
     *
     * @since   3.0.0
     */
    private static function normalizeKey(string $key): string
    {
        $key = preg_replace('/([a-z0-9])([A-Z])/', '$1-$2', $key) ?? $key;
        $key = str_replace('_', '-', $key);

        return strtolower($key);
    }
}
