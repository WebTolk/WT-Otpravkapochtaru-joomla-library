<?php

/**
 * @package     WT Otpravkapochtaru
 * @version     3.0.0
 * @author      Sergey Tolkachyov
 * @copyright   Copyright (c) 2026 Sergey Tolkachyov, WebTolk. All rights reserved.
 * @license     GNU/GPL 3.0
 * @since       0.1.0
 */

defined('_JEXEC') or die;

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScriptInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Version;
use Joomla\Database\DatabaseDriver;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

return new class () implements ServiceProviderInterface {
    public function register(Container $container): void
    {
        $container->set(
            InstallerScriptInterface::class,
            new class ($container->get(AdministratorApplication::class)) implements InstallerScriptInterface {
                protected AdministratorApplication $app;

                protected DatabaseDriver $db;

                protected string $minimumJoomla = '5.0';

                protected string $minimumPhp = '8.1';

                public function __construct(AdministratorApplication $app)
                {
                    $this->app = $app;
                    $this->db  = Factory::getContainer()->get(DatabaseDriver::class);
                }

                public function install(InstallerAdapter $adapter): bool
                {
                    return true;
                }

                public function uninstall(InstallerAdapter $adapter): bool
                {
                    return true;
                }

                public function update(InstallerAdapter $adapter): bool
                {
                    return true;
                }

                public function preflight(string $type, InstallerAdapter $adapter): bool
                {
                    if (!$this->checkJoomlaVersion()) {
                        return false;
                    }

                    if (!$this->checkPhpVersion()) {
                        return false;
                    }

                    return true;
                }

                public function postflight(string $type, InstallerAdapter $adapter): bool
                {
                    if (in_array($type, ['install', 'discover_install', 'update'], true)) {
                        $this->enablePlugin('wt_otpravkapochtaru', 'system');
                    }

                    $html = '<div class="alert alert-info">'
                        . Text::_('PKG_LIB_WT_OTPRAVKAPOCHTARU_INSTALLATION_FINISHED')
                        . '</div>';

                    $this->app->enqueueMessage($html, 'info');

                    return true;
                }

                protected function enablePlugin(string $element, string $folder): void
                {
                    $plugin          = new stdClass();
                    $plugin->type    = 'plugin';
                    $plugin->element = $element;
                    $plugin->folder  = $folder;
                    $plugin->enabled = 1;

                    $this->db->updateObject('#__extensions', $plugin, ['type', 'element', 'folder']);
                }

                protected function checkJoomlaVersion(): bool
                {
                    $version = new Version();

                    if (version_compare($version->getShortVersion(), $this->minimumJoomla, '>=')) {
                        return true;
                    }

                    $this->app->enqueueMessage(
                        Text::sprintf(
                            'PKG_LIB_WT_OTPRAVKAPOCHTARU_ERROR_MINIMUM_JOOMLA',
                            $this->minimumJoomla
                        ),
                        'error'
                    );

                    return false;
                }

                protected function checkPhpVersion(): bool
                {
                    if (version_compare(PHP_VERSION, $this->minimumPhp, '>=')) {
                        return true;
                    }

                    $this->app->enqueueMessage(
                        Text::sprintf(
                            'PKG_LIB_WT_OTPRAVKAPOCHTARU_ERROR_MINIMUM_PHP',
                            $this->minimumPhp
                        ),
                        'error'
                    );

                    return false;
                }
            }
        );
    }
};
