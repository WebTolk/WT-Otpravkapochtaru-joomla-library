<?php

/**
 * Read runtime credentials from plugin params or an existing legacy provider.
 *
 * @package     WT Otpravkapochtaru
 * @version     3.0.0
 * @author      Sergey Tolkachyov
 * @copyright   Copyright (c) 2026 Sergey Tolkachyov, WebTolk. All rights reserved.
 * @license     GNU/GPL 3.0
 */

namespace Webtolk\Otpravkapochtaru\Joomla;

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

/**
 * Thin wrapper credentials source.
 */
final class CredentialsProvider
{
    private const PLUGIN_GROUP = 'system';
    private const PLUGIN_NAME = 'wtotpravkapochtaru';

    private Registry $params;

    /**
     * Accept legacy provider objects, explicit arrays, Registry instances, or read plugin params.
     *
     * @param array<string, mixed>|Registry|object|null $source
     */
    public function __construct(array|object|null $source = null)
    {
        $this->initFromSource($source);
    }

    public function getAccessToken(): string
    {
        $value = trim((string) $this->params()->get('access_token', ''));

        if ($value === '') {
            $value = trim((string) $this->params()->get('AccessToken', ''));
        }

        if ($value === '') {
            throw new \RuntimeException('Required configuration value "access_token" is missing.');
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

    /**
     * Build the `X-User-Authorization` header value.
     *
     * User-key mode returns the key as is; login/password mode returns a base64 encoded pair.
     */
    public function getUserAuthorizationHeader(): string
    {
        if ($this->getAuthMode() === 'key' || $this->getAuthMode() === 'user_key') {
            $value = trim($this->getUserKey());

            if ($value === '') {
                throw new \RuntimeException('Required configuration value "user_key" or "user_auth_key" is missing.');
            }

            return $value;
        }

        $login = trim((string) $this->params()->get('user_login', ''));

        if ($login === '') {
            throw new \RuntimeException('Required configuration value "user_login" is missing.');
        }

        $password = trim((string) $this->params()->get('user_password', ''));

        if ($password === '') {
            throw new \RuntimeException('Required configuration value "user_password" is missing.');
        }

        return base64_encode($login . ':' . $password);
    }

    public function params(): Registry
    {
        if (isset($this->params)) {
            return $this->params;
        }

        $this->params = $this->loadFromPlugin();

        return $this->params;
    }

    private function initFromSource(array|object|null $source): void
    {
        if ($source instanceof Registry) {
            $this->params = $source;

            return;
        }

        if (is_array($source)) {
            $this->params = new Registry($source);

            return;
        }

        if (is_object($source) && method_exists($source, 'params')) {
            try {
                $params = $source->params();
            } catch (\Throwable $exception) {
                throw new \RuntimeException(
                    'Failed to read legacy credentials source: ' . $exception->getMessage(),
                    0,
                    $exception
                );
            }

            if ($params instanceof Registry) {
                $this->params = $params;
            }
        }
    }

    private function loadFromPlugin(): Registry
    {
        if (!PluginHelper::isEnabled(self::PLUGIN_GROUP, self::PLUGIN_NAME)) {
            throw new \RuntimeException('System plugin wtotpravkapochtaru is disabled.');
        }

        $plugin = PluginHelper::getPlugin(self::PLUGIN_GROUP, self::PLUGIN_NAME);

        if ($plugin === null || empty($plugin->params)) {
            throw new \RuntimeException('System plugin wtotpravkapochtaru configuration is empty.');
        }

        return new Registry($plugin->params);
    }
}
