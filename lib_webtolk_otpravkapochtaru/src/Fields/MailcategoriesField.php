<?php

/**
 * Joomla list field with mail categories for selected OPS + mail type.
 *
 * @package     WT Otpravkapochtaru
 * @version     3.0.0
 * @author      Sergey Tolkachyov
 * @copyright   Copyright (c) 2026 Sergey Tolkachyov, WebTolk. All rights reserved.
 * @license     GNU/GPL 3.0
 * @since       3.0.0
 */

namespace Webtolk\Otpravkapochtaru\Fields;

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Webtolk\Otpravkapochtaru\Joomla\CredentialsProvider;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;
use Webtolk\Otpravkapochtaru\Service\LinkedSelectOptionsService;

/**
 * List field with mail categories for selected OPS and mail type.
 *
 * @since  3.0.0
 */
final class MailcategoriesField extends LinkedSelectField
{
    /**
     * Field type used by Joomla when resolving XML field definitions.
     *
     * @var    string
     * @since  3.0.0
     */
    protected $type = 'mailcategories';

    /**
     * Build mail category options for selected shipment point and selected type.
     *
     * @return  array<int, object>
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

        $optionsService = new LinkedSelectOptionsService();
        $postofficeCode = $this->getRequestFieldValue('postoffice_code');
        $mailType       = $this->getRequestFieldValue('mail_type');

        if ($postofficeCode === '' || $mailType === '') {
            $currentValue = $this->getCurrentValue();
            if ($currentValue === '') {
                return [
                    HTMLHelper::_('select.option', '', Text::_('JGLOBAL_SELECT_AN_OPTION')),
                ];
            }

            $optionText = $optionsService->getMailCategoryLabel($currentValue);

            return [
                HTMLHelper::_('select.option', $currentValue, $optionText),
            ];
        }

        try {
            $apiClient      = new Otpravkapochtaru(new CredentialsProvider());
            $shippingPoints = $apiClient->getShippingPoints();
        } catch (\RuntimeException $e) {
            if ($this->isConfigurationError($e)) {
                return [
                    HTMLHelper::_('select.option', '', Text::_('PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_OPSLIST_CONFIG_MISSING')),
                ];
            }

            return [
                HTMLHelper::_('select.option', '', Text::_('PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_OPSLIST_API_ERROR')),
            ];
        } catch (\Throwable) {
            return [
                HTMLHelper::_('select.option', '', Text::_('PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_OPSLIST_API_ERROR')),
            ];
        }

        if (!is_array($shippingPoints) || $shippingPoints === []) {
            return [
                HTMLHelper::_('select.option', '', Text::_('PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_OPSLIST_EMPTY')),
            ];
        }

        $optionsData = $optionsService->getMailCategoryOptions(
            $shippingPoints,
            $postofficeCode,
            $mailType
        );

        $options = [];

        foreach ($optionsData as $optionData) {
            $value = (string) ($optionData['value'] ?? '');
            $text  = (string) ($optionData['text'] ?? '');

            if ($value === '') {
                continue;
            }

            $options[] = HTMLHelper::_('select.option', $value, $text);
        }

        if ($options === []) {
            return [
                HTMLHelper::_('select.option', '', Text::_('PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_OPSLIST_EMPTY')),
            ];
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
