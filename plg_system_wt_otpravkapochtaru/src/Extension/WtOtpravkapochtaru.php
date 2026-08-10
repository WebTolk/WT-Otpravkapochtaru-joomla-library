<?php

/**
 * System plugin shell that provides configuration and language loading for the library package.
 *
 * @package     WT Otpravkapochtaru
 * @version     3.0.0
 * @author      Sergey Tolkachyov
 * @copyright   Copyright (c) 2026 Sergey Tolkachyov, WebTolk. All rights reserved.
 * @license     GNU/GPL 3.0
 * @since       0.1.0
 */

namespace Webtolk\Plugin\System\WtOtpravkapochtaru\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Session\Session;
use Webtolk\Otpravkapochtaru\Configuration\CredentialsProvider;
use Webtolk\Otpravkapochtaru\Exception\ConfigurationException;
use Webtolk\Otpravkapochtaru\Exception\TransportException;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;
use Webtolk\Otpravkapochtaru\Service\LinkedSelectOptionsService;

final class WtOtpravkapochtaru extends CMSPlugin
{
    /**
     * Ask Joomla to load plugin language files when the plugin is instantiated.
     *
     * @var    bool
     * @since  3.0.0
     */
    protected $autoloadLanguage = true;

    /**
     * Plugin event handler for Joomla's com_ajax endpoint.
     *
     * @param   mixed  $event  Joomla event object on Joomla 4/5/6, unused on older dispatch paths.
     *
     * @return  array<string, mixed>
     *
     * @since   3.0.0
     */
    public function onAjaxWtotpravkapochtaru(mixed $event = null): array
    {
        $result = $this->handleAjaxRequest();

        if ($result instanceof \Throwable) {
            throw $result;
        }

        if (is_object($event) && method_exists($event, 'updateEventResult')) {
            $event->updateEventResult($result);
        }

        return $result;
    }

    /**
     * Handle AJAX request, validate input, and return structured array or error.
     *
     * @return  array<string, mixed>|\Throwable
     *
     * @since   3.0.0
     */
    private function handleAjaxRequest(): array|\Throwable
    {
        $app   = $this->getApplication();
        $input = $app->getInput();

        $method = strtoupper((string) $input->getMethod());

        if (!in_array($method, ['GET', 'POST'], true)) {
            $app->setHeader('status', 405, true);

            return new \InvalidArgumentException(Text::_('PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_AJAX_INVALID_METHOD'), 405);
        }

        if (!Session::checkToken('get') && !Session::checkToken('post')) {
            $app->setHeader('status', 403, true);

            return new \InvalidArgumentException(Text::_('JINVALID_TOKEN'), 403);
        }

        $action = $input->get('action', '', 'cmd');

        if (!in_array($action, ['getMailTypes', 'getMailCategories'], true)) {
            $app->setHeader('status', 400, true);

            return new \InvalidArgumentException(Text::_('PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_AJAX_UNSUPPORTED_ACTION'), 400);
        }

        $postOfficeCode = trim((string) $input->get('postoffice_code', '', 'raw'));

        if ($postOfficeCode === '') {
            $app->setHeader('status', 422, true);

            return new \InvalidArgumentException(Text::_('PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_AJAX_MISSING_POSTOFFICE_CODE'), 422);
        }

        if (!preg_match('/^\d{6}$/', $postOfficeCode)) {
            $app->setHeader('status', 400, true);

            return new \InvalidArgumentException(Text::_('PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_AJAX_INVALID_POSTOFFICE_CODE'), 400);
        }

        try {
            if (!PluginHelper::isEnabled('system', 'wtotpravkapochtaru')) {
                $app->setHeader('status', 403, true);

                return new \RuntimeException(Text::_('PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_AJAX_PLUGIN_DISABLED'), 403);
            }

            $apiClient      = new Otpravkapochtaru(new CredentialsProvider());
            $shippingPoints = $apiClient->getShippingPoints();
        } catch (ConfigurationException) {
            $app->setHeader('status', 403, true);

            return new \RuntimeException(Text::_('PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_AJAX_CONFIG_MISSING'), 403);
        } catch (TransportException | \RuntimeException) {
            $app->setHeader('status', 502, true);

            return new \RuntimeException(Text::_('PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_AJAX_API_UNAVAILABLE'), 502);
        } catch (\Throwable) {
            $app->setHeader('status', 500, true);

            return new \RuntimeException(Text::_('PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_AJAX_INTERNAL_ERROR'), 500);
        }

        if (!is_array($shippingPoints)) {
            $app->setHeader('status', 502, true);

            return new \RuntimeException(Text::_('PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_AJAX_INTERNAL_ERROR'), 502);
        }

        $optionsService  = new LinkedSelectOptionsService();
        $mailTypeOptions = $optionsService->getMailTypeOptions($shippingPoints, $postOfficeCode);

        if ($action === 'getMailTypes' || $mailTypeOptions === []) {
            return ['options' => $mailTypeOptions];
        }

        $mailType = trim((string) $input->get('mail_type', '', 'raw'));

        if ($mailType === '') {
            $app->setHeader('status', 422, true);

            return new \InvalidArgumentException(Text::_('PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_AJAX_MISSING_MAIL_TYPE'), 422);
        }

        $mailTypeList = array_map(
            static fn (array $option): string => (string) ($option['value'] ?? ''),
            $mailTypeOptions
        );

        if (!in_array($mailType, $mailTypeList, true)) {
            $app->setHeader('status', 422, true);

            return new \InvalidArgumentException(Text::_('PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_AJAX_INVALID_MAIL_TYPE'), 422);
        }

        return ['options' => $optionsService->getMailCategoryOptions($shippingPoints, $postOfficeCode, $mailType)];
    }
}
