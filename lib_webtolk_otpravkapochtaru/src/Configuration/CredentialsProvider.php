<?php

/**
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

    private ?Registry $params = null;

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

    public function getAuthMode(): string
    {
        return (string) $this->params()->get(
            'auth_mode',
            $this->params()->get('user_key_or_login_and_password', 'key')
        );
    }

    public function getUserKey(): string
    {
        return (string) $this->params()->get('user_key', $this->params()->get('user_auth_key', ''));
    }

    public function getUserLogin(): string
    {
        return $this->params()->get('user_login', '');
    }

    public function getUserPassword(): string
    {
        return $this->params()->get('user_password', '');
    }

    public function getTrackingLogin(): string
    {
        return $this->params()->get('tracking_login', '');
    }

    public function getTrackingPassword(): string
    {
        return $this->params()->get('tracking_password', '');
    }

    public function getHttpTimeout(): int
    {
        return (int) $this->params()->get('http_timeout', 60);
    }

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
