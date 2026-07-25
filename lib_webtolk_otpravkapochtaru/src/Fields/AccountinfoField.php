<?php

/**
 * Joomla note field that checks and displays Russian Post account/API status in plugin settings.
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

use Joomla\CMS\Form\Field\NoteField;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Webtolk\Otpravkapochtaru\Configuration\CredentialsProvider;
use Webtolk\Otpravkapochtaru\Exception\ConfigurationException;
use Webtolk\Otpravkapochtaru\Exception\TransportException;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

final class AccountinfoField extends NoteField
{
    /**
     * Field type used by Joomla when resolving XML field definitions.
     *
     * @var    string
     * @since  3.0.0
     */
    protected $type = 'accountinfo';

    /**
     * Render account information, API status and API limit details for the current form parameters.
     *
     * The field reports configuration, authorization and transport problems inline instead of breaking
     * the plugin edit form.
     *
     * @return  string  Rendered administrator HTML.
     *
     * @since   3.0.0
     */
    protected function getInput(): string
    {
        $apiClient = new Otpravkapochtaru(new CredentialsProvider($this->getFormParams()));

        try {
            $accountInfo = $apiClient->getAccountInfo();
        } catch (ConfigurationException) {
            return $this->renderState('warning', Text::_('PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_ACCOUNT_INFO_CONFIG_MISSING'));
        } catch (TransportException $e) {
            if ($this->isUnauthorized($e)) {
                return $this->renderState('danger', Text::_('PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_ACCOUNT_INFO_UNAUTHORIZED'));
            }

            return $this->renderState(
                'danger',
                Text::_('PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_ACCOUNT_INFO_API_ERROR'),
                $this->escape($e->getMessage())
            );
        } catch (\Exception $e) {
            return $this->renderState(
                'danger',
                Text::_('PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_ACCOUNT_INFO_API_ERROR'),
                $this->escape($e->getMessage())
            );
        }

        if ($accountInfo === []) {
            return $this->renderState(
                'warning',
                Text::_('PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_ACCOUNT_INFO_EMPTY_RESPONSE'),
                Text::_('PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_ACCOUNT_INFO_EMPTY_RESPONSE_DESC')
            );
        }

        if (($accountInfo['sub-code'] ?? '') === 'UNAUTHORIZED') {
            return $this->renderState('danger', Text::_('PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_ACCOUNT_INFO_UNAUTHORIZED'));
        }

        $apiConnected    = (int) ($accountInfo['api_enabled'] ?? 0) === 1;
        $apiLimit        = null;
        $apiLimitWarning = null;

        if ($apiConnected) {
            try {
                $limitResponse = $apiClient->getApiLimit();

                if ($limitResponse !== []) {
                    $apiLimit = $limitResponse;
                } else {
                    $apiLimitWarning = Text::_('PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_ACCOUNT_INFO_API_RESTRICTED');
                }
            } catch (TransportException | \Exception) {
                $apiLimitWarning = Text::_('PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_ACCOUNT_INFO_API_RESTRICTED');
            }
        }

        $statusClass = $apiConnected ? 'success' : 'warning';
        $statusText  = Text::_(
            $apiConnected
            ? 'PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_ACCOUNT_INFO_API_CONNECTED'
            : 'PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_ACCOUNT_INFO_API_NOT_CONNECTED'
        );

        $agreementNumber = is_scalar($accountInfo['agreement-number'] ?? null) ? trim((string) $accountInfo['agreement-number']) : '';
        $agreementDate   = is_scalar($accountInfo['agreement-date'] ?? null) ? trim((string) $accountInfo['agreement-date']) : '';
        $agreement       = $agreementNumber;

        if ($agreementDate !== '') {
            $agreement = trim($agreementNumber . ' ' . Text::_('PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_ACCOUNT_INFO_AGREEMENT_FROM') . ' ' . $agreementDate);
        }

        $email    = '';
        $accounts = $accountInfo['accounts'] ?? [];

        if (is_array($accounts)) {
            foreach ($accounts as $account) {
                if (!is_array($account)) {
                    continue;
                }

                $candidate = trim((string) ($account['email'] ?? ''));

                if ($candidate !== '') {
                    $email = $candidate;
                    break;
                }
            }
        }

        $html   = [];
        $html[] = '<div class="card shadow-sm border-0">';
        $html[] = '<div class="card-body">';
        $html[] = '<div class="small text-muted mb-3">' . $this->escape(Text::_('PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_ACCOUNT_INFO_BRAND')) . '</div>';
        $html[] = '<div class="row g-3">';
        $html[] = '<div class="col-12 col-lg-6">';
        $html[] = '<ul class="list-group list-group-flush">';
        $html[] = '<li class="list-group-item"><strong>' . $this->escape(Text::_('PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_ACCOUNT_INFO_ORG_NAME')) . '</strong> ' . $this->escape(is_scalar($accountInfo['org-name'] ?? null) ? trim((string) $accountInfo['org-name']) : '-') . '</li>';
        $html[] = '<li class="list-group-item"><strong>' . $this->escape(Text::_('PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_ACCOUNT_INFO_ORG_INN')) . '</strong> ' . $this->escape(is_scalar($accountInfo['org-inn'] ?? null) ? trim((string) $accountInfo['org-inn']) : '-') . '</li>';
        $html[] = '<li class="list-group-item"><strong>' . $this->escape(Text::_('PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_ACCOUNT_INFO_ORG_KPP')) . '</strong> ' . $this->escape(is_scalar($accountInfo['org-kpp'] ?? null) ? trim((string) $accountInfo['org-kpp']) : '-') . '</li>';
        $html[] = '<li class="list-group-item"><strong>' . $this->escape(Text::_('PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_ACCOUNT_INFO_EMAIL')) . '</strong> ' . $this->escape($email !== '' ? $email : '-') . '</li>';
        $html[] = '<li class="list-group-item"><strong>' . $this->escape(Text::_('PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_ACCOUNT_INFO_AGREEMENT_NUMBER')) . '</strong> ' . $this->escape($agreement !== '' ? $agreement : '-') . '</li>';
        $html[] = '<li class="list-group-item"><strong>' . $this->escape(Text::_('PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_ACCOUNT_INFO_ESPP')) . '</strong> ' . $this->escape(is_scalar($accountInfo['espp-code'] ?? null) ? trim((string) $accountInfo['espp-code']) : '-') . '</li>';
        $html[] = '</ul>';
        $html[] = '</div>';
        $html[] = '<div class="col-12 col-lg-6">';
        $html[] = '<div class="alert alert-' . $statusClass . ' mb-3">';
        $html[] = '<div class="fw-semibold">' . $this->escape($statusText) . '</div>';
        $html[] = '<div class="small">' . $this->escape(Text::_('PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_ACCOUNT_INFO_API_HOST')) . '</div>';
        $html[] = '</div>';

        if ($apiLimit !== null) {
            $allowed = (int) ($apiLimit['allowed-count'] ?? 0);
            $used    = (int) ($apiLimit['current-count'] ?? 0);

            $html[] = '<ul class="list-group list-group-flush">';
            $html[] = '<li class="list-group-item"><strong>' . $this->escape(Text::_('PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_ACCOUNT_INFO_API_LIMIT')) . '</strong> ' . $allowed . '</li>';
            $html[] = '<li class="list-group-item"><strong>' . $this->escape(Text::_('PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_ACCOUNT_INFO_API_USED')) . '</strong> ' . $used . '</li>';
            $html[] = '<li class="list-group-item"><strong>' . $this->escape(Text::_('PLG_SYSTEM_WT_OTPRAVKAPOCHTARU_ACCOUNT_INFO_API_REMAINING')) . '</strong> ' . max(0, $allowed - $used) . '</li>';
            $html[] = '</ul>';
        } elseif ($apiLimitWarning !== null) {
            $html[] = '<div class="alert alert-secondary mb-0">' . $this->escape($apiLimitWarning) . '</div>';
        }

        $html[] = '</div>';
        $html[] = '</div>';
        $html[] = '</div>';
        $html[] = '</div>';

        return implode('', $html);
    }

    /**
     * Reuse the label text as the Joomla field title.
     *
     * @return  string  The field title.
     *
     * @since   3.0.0
     */
    protected function getTitle(): string
    {
        return $this->getLabel();
    }

    /**
     * Hide the regular label because the field renders a complete status card.
     *
     * @return  string  The field label markup.
     *
     * @since   3.0.0
     */
    protected function getLabel(): string
    {
        return '';
    }

    /**
     * Read unsaved plugin params from the current edit form.
     *
     * @return  Registry  Current form parameters.
     *
     * @since   3.0.0
     */
    private function getFormParams(): Registry
    {
        $data = $this->form->getData();

        return new Registry($data->get('params', []));
    }

    /**
     * Render a Bootstrap alert for configuration or API status messages.
     *
     * @param   string       $level    Bootstrap alert level.
     * @param   string       $message  Main status message.
     * @param   string|null  $details  Optional raw details already prepared for output.
     *
     * @return  string  Rendered alert markup.
     *
     * @since   3.0.0
     */
    private function renderState(string $level, string $message, ?string $details = null): string
    {
        $html   = [];
        $html[] = '<div class="alert alert-' . $this->escape($level) . ' mb-0">';
        $html[] = '<div class="fw-semibold">' . $this->escape($message) . '</div>';

        if ($details !== null && $details !== '') {
            $html[] = '<div class="small mt-1">' . $details . '</div>';
        }

        $html[] = '</div>';

        return implode('', $html);
    }

    /**
     * Detect authorization failures from HTTP status or Russian Post error text.
     *
     * @param   TransportException  $exception  Transport error raised by the API client.
     *
     * @return  bool
     *
     * @since   3.0.0
     */
    private function isUnauthorized(TransportException $exception): bool
    {
        return $exception->getCode() === 401
            || str_contains(strtoupper($exception->getMessage()), 'UNAUTHORIZED');
    }

    /**
     * Escape dynamic values before injecting them into Joomla administrator HTML.
     *
     * @param   mixed  $value  Dynamic value to escape.
     *
     * @return  string
     *
     * @since   3.0.0
     */
    private function escape(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
