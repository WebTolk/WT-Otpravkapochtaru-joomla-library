<?php

/**
 * Read runtime credentials from plugin params or an existing legacy provider.
 *
 * @package     WT Otpravkapochtaru
 * @version     3.0.0
 * @author      Sergey Tolkachyov
 * @copyright   Copyright (c) 2026 Sergey Tolkachyov, WebTolk. All rights reserved.
 * @license     GNU/GPL 3.0
 * @since       3.0.0
 */

namespace Webtolk\Otpravkapochtaru\Joomla;

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Registry\Registry;

/**
 * Thin wrapper credentials source.
 *
 * @since  3.0.0
 */
final class CredentialsProvider
{
    /**
     * Joomla plugin group used as the default credentials source.
     *
     * @since  3.0.0
     */
    private const PLUGIN_GROUP = 'system';

    /**
     * Joomla plugin element used as the default credentials source.
     *
     * @since  3.0.0
     */
    private const PLUGIN_NAME = 'wtotpravkapochtaru';

    /**
     * Resolved configuration values.
     *
     * @since  3.0.0
     */
    private Registry $params;

    /**
     * Accept legacy provider objects, explicit arrays, Registry instances, or read plugin params.
     *
     * @param   array<string, mixed>|Registry|object|null  $source  Explicit configuration source.
     *
     * @since   3.0.0
     */
    public function __construct(array|object|null $source = null)
    {
        $this->initFromSource($source);
    }

    /**
     * Return the REST access token.
     *
     * @return  string
     *
     * @throws  \RuntimeException
     *
     * @since   3.0.0
     */
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

    /**
     * Return selected user authorization mode.
     *
     * @return  string
     *
     * @since   3.0.0
     */
    public function getAuthMode(): string
    {
        return (string) $this->params()->get(
            'auth_mode',
            $this->params()->get('user_key_or_login_and_password', 'key')
        );
    }

    /**
     * Return user authorization key.
     *
     * @return  string
     *
     * @since   3.0.0
     */
    public function getUserKey(): string
    {
        return (string) $this->params()->get('user_key', $this->params()->get('user_auth_key', ''));
    }

    /**
     * Return user login for login/password authorization mode.
     *
     * @return  string
     *
     * @since   3.0.0
     */
    public function getUserLogin(): string
    {
        return $this->params()->get('user_login', '');
    }

    /**
     * Return user password for login/password authorization mode.
     *
     * @return  string
     *
     * @since   3.0.0
     */
    public function getUserPassword(): string
    {
        return $this->params()->get('user_password', '');
    }

    /**
     * Return SOAP tracking login.
     *
     * @return  string
     *
     * @since   3.0.0
     */
    public function getTrackingLogin(): string
    {
        return $this->params()->get('tracking_login', '');
    }

    /**
     * Return SOAP tracking password.
     *
     * @return  string
     *
     * @since   3.0.0
     */
    public function getTrackingPassword(): string
    {
        return $this->params()->get('tracking_password', '');
    }

    /**
     * Return configured HTTP timeout.
     *
     * @return  int
     *
     * @since   3.0.0
     */
    public function getHttpTimeout(): int
    {
        return (int) $this->params()->get('http_timeout', 60);
    }

    /**
     * Build the `X-User-Authorization` header value.
     *
     * User-key mode returns the key as is; login/password mode returns a base64 encoded pair.
     *
     * @return  string
     *
     * @throws  \RuntimeException
     *
     * @since   3.0.0
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

    /**
     * Return resolved credentials registry.
     *
     * @return  Registry
     *
     * @throws  \RuntimeException
     *
     * @since   3.0.0
     */
    public function params(): Registry
    {
        if (isset($this->params)) {
            return $this->params;
        }

        $this->params = $this->loadFromPlugin();

        return $this->params;
    }

    /**
     * Initialize credentials from an explicit source.
     *
     * @param   array<string, mixed>|object|null  $source  Explicit configuration source.
     *
     * @return  void
     *
     * @throws  \RuntimeException
     *
     * @since   3.0.0
     */
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

    /**
     * Load credentials from the enabled system plugin.
     *
     * @return  Registry
     *
     * @throws  \RuntimeException
     *
     * @since   3.0.0
     */
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
