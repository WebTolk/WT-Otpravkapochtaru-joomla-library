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
use Webtolk\Otpravkapochtaru\Configuration\CredentialsProvider;
use Webtolk\Otpravkapochtaru\Exception\ConfigurationException;
use Webtolk\Otpravkapochtaru\Exception\TransportException;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

final class OpslistField extends ListField
{
    protected $type = 'opslist';

    /**
     * Build select options from `getShippingPoints()` and return user-facing error options on failures.
     *
     * @since 3.0.0
     */
    protected function getOptions(): array
    {
        if (!PluginHelper::isEnabled('system', 'wt_otpravkapochtaru')) {
            return [
                HTMLHelper::_('select.option', '', Text::_('PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_OPSLIST_PLUGIN_DISABLED')),
            ];
        }

        try {
            $apiClient      = new Otpravkapochtaru(new CredentialsProvider());
            $shippingPoints = $apiClient->getShippingPoints();
        } catch (ConfigurationException $e) {
            return [
                HTMLHelper::_('select.option', '', Text::_('PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_OPSLIST_CONFIG_MISSING') . ' - ' . htmlspecialchars((string) $e->getMessage(), ENT_QUOTES, 'UTF-8')),
            ];
        } catch (TransportException | \Exception $e) {
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
}
