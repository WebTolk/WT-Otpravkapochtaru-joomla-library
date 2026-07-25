<?php

/**
 * Reads Russian Post credentials from explicit params or from the Joomla system plugin.
 *
 * @package     WT Otpravkapochtaru
 * @version     3.0.0
 * @author      Sergey Tolkachyov
 * @copyright   Copyright (c) 2026 Sergey Tolkachyov, WebTolk. All rights reserved.
 * @license     GNU/GPL 3.0
 * @since       0.1.0
 */

namespace Webtolk\Otpravkapochtaru\Configuration;

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;
use Webtolk\Otpravkapochtaru\Exception\ConfigurationException;

final class CredentialsProvider
{
    private const PLUGIN_GROUP = 'system';
    private const PLUGIN_NAME  = 'wt_otpravkapochtaru';

    /**
     * Explicit or lazily loaded Joomla plugin parameters.
     *
     * @var    Registry|null
     * @since  3.0.0
     */
    private ?Registry $params = null;

    /**
     * Accept explicit params for tests/direct usage or defer loading to the Joomla plugin.
     *
     * @param   array<string, mixed>|Registry|null  $params  Explicit credentials source.
     *
     * @since   3.0.0
     */
    public function __construct(array|Registry|null $params = null)
    {
        if ($params instanceof Registry) {
            $this->params = $params;

            return;
        }

        if (is_array($params)) {
            $this->params = new Registry($params);
        }
    }

    /**
     * Return the Otpravka API access token and support the legacy `AccessToken` parameter name.
     *
     * @since 3.0.0
     */
    public function getAccessToken(): string
    {
        $value = trim((string) $this->params()->get('access_token', ''));

        if ($value === '') {
            $value = trim((string) $this->params()->get('AccessToken', ''));
        }

        if ($value === '') {
            throw new ConfigurationException('Required configuration value "access_token" is missing.');
        }

        return $value;
    }

    /**
     * Return the selected user authorization mode.
     *
     * New `auth_mode` params and legacy `user_key_or_login_and_password` params are both supported.
     *
     * @since 3.0.0
     */
    public function getAuthMode(): string
    {
        return (string) $this->params()->get(
            'auth_mode',
            $this->params()->get('user_key_or_login_and_password', 'key')
        );
    }

    /**
     * Return the raw user key value without validating that the selected auth mode uses it.
     *
     * @since 3.0.0
     */
    public function getUserKey(): string
    {
        return (string) $this->params()->get('user_key', $this->params()->get('user_auth_key', ''));
    }

    /**
     * Return the raw Otpravka API login from plugin parameters.
     *
     * @since 3.0.0
     */
    public function getUserLogin(): string
    {
        return $this->params()->get('user_login', '');
    }

    /**
     * Return the raw Otpravka API password from plugin parameters.
     *
     * @since 3.0.0
     */
    public function getUserPassword(): string
    {
        return $this->params()->get('user_password', '');
    }

    /**
     * Return the SOAP tracking login used by Russian Post tracking services.
     *
     * @since 3.0.0
     */
    public function getTrackingLogin(): string
    {
        return $this->params()->get('tracking_login', '');
    }

    /**
     * Return the SOAP tracking password used by Russian Post tracking services.
     *
     * @since 3.0.0
     */
    public function getTrackingPassword(): string
    {
        return $this->params()->get('tracking_password', '');
    }

    /**
     * Return HTTP/SOAP timeout in seconds, falling back to one minute.
     *
     * @since 3.0.0
     */
    public function getHttpTimeout(): int
    {
        return (int) $this->params()->get('http_timeout', 60);
    }

    /**
     * Build the `X-User-Authorization` header value.
     *
     * User-key mode returns the key as is; login/password mode returns a base64 encoded pair.
     * Missing values fail early with ConfigurationException to avoid opaque API authorization errors.
     *
     * @since 3.0.0
     */
    public function getUserAuthorizationHeader(): string
    {
        if ($this->getAuthMode() === 'key' || $this->getAuthMode() === 'user_key') {
            $value = trim((string) $this->params()->get('user_key', ''));

            if ($value === '') {
                throw new ConfigurationException('Required configuration value "user_key" is missing.');
            }

            return $value;
        }

        $login = trim((string) $this->params()->get('user_login', ''));

        if ($login === '') {
            throw new ConfigurationException('Required configuration value "user_login" is missing.');
        }

        $password = trim((string) $this->params()->get('user_password', ''));

        if ($password === '') {
            throw new ConfigurationException('Required configuration value "user_password" is missing.');
        }

        return base64_encode($login . ':' . $password);
    }

    /**
     * Return cached params or load them from the enabled system plugin.
     *
     * @since 3.0.0
     */
    public function params(): Registry
    {
        if ($this->params instanceof Registry) {
            return $this->params;
        }

        if (!PluginHelper::isEnabled(self::PLUGIN_GROUP, self::PLUGIN_NAME)) {
            throw new ConfigurationException('System plugin wt_otpravkapochtaru is disabled.');
        }

        $plugin = PluginHelper::getPlugin(self::PLUGIN_GROUP, self::PLUGIN_NAME);

        if ($plugin === null || empty($plugin->params)) {
            throw new ConfigurationException('System plugin wt_otpravkapochtaru configuration is empty.');
        }

        $this->params = new Registry($plugin->params);

        return $this->params;
    }
}
