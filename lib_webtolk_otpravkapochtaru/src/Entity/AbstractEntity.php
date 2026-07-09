<?php

/**
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
    abstract public function toArray(): array;

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

    final protected static function requireNonEmpty(mixed $value, string $message): mixed
    {
        if ($value === null || $value === '' || $value === []) {
            throw new ValidationException($message);
        }

        return $value;
    }

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

    private static function normalizeKey(string $key): string
    {
        $key = preg_replace('/([a-z0-9])([A-Z])/', '$1-$2', $key) ?? $key;
        $key = str_replace('_', '-', $key);

        return strtolower($key);
    }
}
