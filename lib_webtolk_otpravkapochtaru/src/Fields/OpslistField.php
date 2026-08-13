<?php

/**
 * Joomla list field that loads available Russian Post shipping points from the configured API account.
 *
 * @package     WT Otpravkapochtaru
 * @version     3.0.0
 * @author      Sergey Tolkachyov
 * @copyright   Copyright (c) 2026 Sergey Tolkachyov, WebTolk. All rights reserved.
 * @license     GNU/GPL 3.0
 * @since       0.1.0
 */

namespace Webtolk\Otpravkapochtaru\Fields;

defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Webtolk\Otpravkapochtaru\Joomla\CredentialsProvider;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

/**
 * List field that loads the configured account shipping points.
 *
 * @since  0.1.0
 */
final class OpslistField extends ListField
{
    /**
     * Field type used by Joomla when resolving XML field definitions.
     *
     * @var    string
     * @since  3.0.0
     */
    protected $type = 'opslist';

    /**
     * Build select options from upstream `shippingPoints()` and return user-facing error options on failures.
     *
     * @return  array<int, object>  Joomla select option objects.
     *
     * @since   3.0.0
     */
    protected function getOptions(): array
    {
        if (!PluginHelper::isEnabled('system', 'wtotpravkapochtaru')) {
            return [
                HTMLHelper::_('select.option', '', Text::_('PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_OPSLIST_PLUGIN_DISABLED')),
            ];
        }

        try {
            $apiClient      = new Otpravkapochtaru(new CredentialsProvider());
            $shippingPoints = $apiClient->otpravkaApi()->shippingPoints();
        } catch (\RuntimeException $e) {
            if ($this->isConfigurationError($e)) {
                return [
                    HTMLHelper::_('select.option', '', Text::_('PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_OPSLIST_CONFIG_MISSING') . ' - ' . htmlspecialchars((string) $e->getMessage(), ENT_QUOTES, 'UTF-8')),
                ];
            }

            return [
                HTMLHelper::_('select.option', '', Text::_('PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_OPSLIST_API_ERROR') . ' - ' . htmlspecialchars((string) $e->getMessage(), ENT_QUOTES, 'UTF-8')),
            ];
        } catch (\Throwable $e) {
            return [
                HTMLHelper::_('select.option', '', Text::_('PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_OPSLIST_API_ERROR') . ' - ' . htmlspecialchars((string) $e->getMessage(), ENT_QUOTES, 'UTF-8')),
            ];
        }

        if (!is_array($shippingPoints) || $shippingPoints === []) {
            return [
                HTMLHelper::_('select.option', '', Text::_('PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_OPSLIST_EMPTY')),
            ];
        }

        $options = [];

        foreach ($shippingPoints as $shippingPoint) {
            if (!is_array($shippingPoint)) {
                continue;
            }

            $postalCode = (string) ($shippingPoint['operator-postcode'] ?? '');

            if ($postalCode === '') {
                continue;
            }

            $address = trim((string) ($shippingPoint['ops-address'] ?? $shippingPoint['address'] ?? $shippingPoint['operator-address'] ?? ''));
            $label   = trim($postalCode . ' - ' . $address, ' -');
            $label   = $label === '' ? $postalCode : $label;

            $options[] = HTMLHelper::_('select.option', $postalCode, $label);
        }

        if ($options === []) {
            $options[] = HTMLHelper::_('select.option', '', Text::_('PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_OPSLIST_EMPTY'));
        }

        return $options;
    }

    /**
     * Determine whether the exception is caused by configuration or plugin availability issues.
     *
     * @param   \Throwable  $exception  Thrown exception.
     *
     * @return  bool
     *
     * @since   3.0.0
     */
    private function isConfigurationError(\Throwable $exception): bool
    {
        $message = strtoupper((string) $exception->getMessage());

        return str_contains($message, 'REQUIRED CONFIGURATION VALUE')
            || str_contains($message, 'SYSTEM PLUGIN WTOTPRAVKAPOCHTARU IS DISABLED')
            || str_contains($message, 'SYSTEM PLUGIN WTOTPRAVKAPOCHTARU CONFIGURATION IS EMPTY')
            || str_contains($message, 'TRACKING CREDENTIALS ARE NOT CONFIGURED');
    }
}
