<?php

/**
 * Helpers for linked form field option extraction from shipping-point API payloads.
 *
 * @package     WT Otpravkapochtaru
 * @version     3.0.0
 * @author      Sergey Tolkachyov
 * @copyright   Copyright (c) 2026 Sergey Tolkachyov, WebTolk. All rights reserved.
 * @license     GNU/GPL 3.0
 * @since       3.0.0
 */

namespace Webtolk\Otpravkapochtaru\Service;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

final class LinkedSelectOptionsService
{
    /**
     * Prevent repeated language file load attempts inside one request.
     *
     * @var    bool
     * @since  3.0.0
     */
    private static bool $languageLoaded = false;

    /**
     * Find shipping point object by Russian Post operator postcode.
     *
     * @param   array<int|string, mixed>  $shippingPoints  Raw API response.
     * @param   string                   $postOfficeCode  6 digit code.
     *
     * @return  array<string, mixed>|null
     * @since  3.0.0
     */
    public function findShippingPoint(array $shippingPoints, string $postOfficeCode): ?array
    {
        foreach ($shippingPoints as $shippingPoint) {
            if (!is_array($shippingPoint)) {
                continue;
            }

            $shippingPointCode = (string) (new Registry($shippingPoint))->get('operator-postcode', '');

            if ($shippingPointCode === $postOfficeCode) {
                return $shippingPoint;
            }
        }

        return null;
    }

    /**
     * Return linked mail type options for selected post office.
     *
     * @param   array<int, mixed>  $shippingPoints
     * @param   string             $postOfficeCode
     *
     * @return  array<int, array<string, string>>
     *
     * @since  3.0.0
     */
    public function getMailTypeOptions(array $shippingPoints, string $postOfficeCode): array
    {
        $shippingPoint = $this->findShippingPoint($shippingPoints, $postOfficeCode);

        if (!is_array($shippingPoint) || $shippingPoint === []) {
            return [];
        }

        return $this->buildOptions($this->getMailTypeValues($shippingPoint), true);
    }

    /**
     * Return linked mail category options for selected post office and selected type.
     *
     * @param   array<int, mixed>  $shippingPoints
     * @param   string             $postOfficeCode
     * @param   string             $mailType
     *
     * @return  array<int, array<string, string>>
     *
     * @since  3.0.0
     */
    public function getMailCategoryOptions(array $shippingPoints, string $postOfficeCode, string $mailType): array
    {
        $shippingPoint = $this->findShippingPoint($shippingPoints, $postOfficeCode);

        if (!is_array($shippingPoint) || $shippingPoint === []) {
            return [];
        }

        return $this->buildOptions($this->getMailCategoryValues($shippingPoint, $mailType), false);
    }

    /**
     * Resolve label for a mail type option.
     *
     * @param   string  $mailType
     *
     * @return string
     * @since  3.0.0
     */
    public function getMailTypeLabel(string $mailType): string
    {
        return $this->resolveLabel($mailType, 'PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_MAIL_TYPE_');
    }

    /**
     * Resolve label for a mail category option.
     *
     * @param   string  $mailCategory
     *
     * @return string
     * @since  3.0.0
     */
    public function getMailCategoryLabel(string $mailCategory): string
    {
        return $this->resolveLabel($mailCategory, 'PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_MAIL_CATEGORY_');
    }

    /**
     * Read all available mail types for one shipping point.
     *
     * @param   array<string, mixed>  $shippingPoint
     *
     * @return  array<int, string>
     * @since  3.0.0
     */
    private function getMailTypeValues(array $shippingPoint): array
    {
        $registry  = new Registry($shippingPoint);
        $mailTypes = (array) ArrayHelper::getValue($shippingPoint, 'user-available-mail-types', []);

        $mailTypes = $this->normalizeValues($mailTypes);

        if ($mailTypes !== []) {
            return $mailTypes;
        }

        $products = (array) $registry->get('user-available-products', []);

        if (!is_array($products)) {
            return [];
        }

        $derivedMailTypes = [];

        foreach ($products as $product) {
            if (!is_array($product)) {
                continue;
            }

            $mailType = (string) ArrayHelper::getValue($product, 'mail-type', '');

            if ($mailType !== '') {
                $derivedMailTypes[] = $mailType;
            }
        }

        return $this->normalizeValues($derivedMailTypes);
    }

    /**
     * Read all available categories for one shipping point and one mail type.
     *
     * @param   array<string, mixed>  $shippingPoint
     * @param   string                $mailType
     *
     * @return  array<int, string>
     * @since  3.0.0
     */
    private function getMailCategoryValues(array $shippingPoint, string $mailType): array
    {
        $registry = new Registry($shippingPoint);
        $products = (array) $registry->get('user-available-products', []);

        if (!is_array($products) || $products === []) {
            return [];
        }

        $categories = [];

        foreach ($products as $product) {
            if (!is_array($product)) {
                continue;
            }

            $productMailType = (string) ArrayHelper::getValue($product, 'mail-type', '');

            if ($productMailType !== $mailType) {
                continue;
            }

            $mailCategory = (string) ArrayHelper::getValue($product, 'mail-category', '');

            if ($mailCategory !== '') {
                $categories[] = $mailCategory;
            }
        }

        return $this->normalizeValues($categories);
    }

    /**
     * Build safe select options from normalized values.
     *
     * @param   array<int, string>  $values
     * @param   bool                $isMailType
     *
     * @return  array<int, array<string, string>>
     * @since  3.0.0
     */
    private function buildOptions(array $values, bool $isMailType): array
    {
        $options = [];

        foreach ($values as $value) {
            $label = $isMailType
                ? $this->getMailTypeLabel($value)
                : $this->getMailCategoryLabel($value);

            $options[] = [
                'value' => $value,
                'text'  => $label,
            ];
        }

        return $options;
    }

    /**
     * Normalize values into unique sorted strings.
     *
     * @param   array<int|string, mixed>  $values
     *
     * @return  array<int, string>
     * @since  3.0.0
     */
    private function normalizeValues(array $values): array
    {
        $normalized = [];

        foreach ($values as $value) {
            if (!is_scalar($value)) {
                continue;
            }

            $normalizedValue = trim((string) $value);

            if ($normalizedValue === '') {
                continue;
            }

            $normalized[$normalizedValue] = true;
        }

        ksort($normalized, SORT_STRING);

        return array_keys($normalized);
    }

    /**
     * Resolve label text with Joomla language constant support.
     *
     * @param   string  $value
     * @param   string  $constantPrefix
     *
     * @return  string
     * @since  3.0.0
     */
    private function resolveLabel(string $value, string $constantPrefix): string
    {
        $value = trim($value);

        if ($value === '') {
            return $value;
        }

        $constant = $constantPrefix . strtoupper(preg_replace('/[^A-Z0-9_]/i', '_', $value));

        try {
            $this->loadLanguage();

            $label = Text::_($constant);
        } catch (\Throwable) {
            return $value;
        }

        return $label === $constant ? $value : $label;
    }

    /**
     * Load plugin language constants used by library fields in third-party extension forms.
     *
     * @return  void
     * @since  3.0.0
     */
    private function loadLanguage(): void
    {
        if (self::$languageLoaded || !defined('JPATH_ADMINISTRATOR')) {
            return;
        }

        $language = Factory::getApplication()->getLanguage();

        $language->load('plg_system_wtotpravkapochtaru', JPATH_ADMINISTRATOR);
        self::$languageLoaded = true;
    }
}
