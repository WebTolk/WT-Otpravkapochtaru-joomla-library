<?php

/**
 * Joomla package installer script provider for compatibility checks and post-install messaging.
 *
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
    /**
     * Register the package installer script object in Joomla's dependency injection container.
     *
     * @param   Container  $container  Joomla dependency injection container.
     *
     * @return  void
     *
     * @since   3.0.0
     */
    public function register(Container $container): void
    {
        $container->set(
            InstallerScriptInterface::class,
            new class ($container->get(AdministratorApplication::class)) implements InstallerScriptInterface {
                /**
                 * Joomla administrator application used for installer messages.
                 *
                 * @var    AdministratorApplication
                 * @since  3.0.0
                 */
                protected AdministratorApplication $app;

                /**
                 * Joomla database driver used for extension state changes.
                 *
                 * @var    DatabaseDriver
                 * @since  3.0.0
                 */
                protected DatabaseDriver $db;

                /**
                 * Minimum supported Joomla version.
                 *
                 * @var    string
                 * @since  3.0.0
                 */
                protected string $minimumJoomla = '5.0';

                /**
                 * Minimum supported PHP version.
                 *
                 * @var    string
                 * @since  3.0.0
                 */
                protected string $minimumPhp = '8.1';

                /**
                 * Store Joomla application and database services used by installer lifecycle hooks.
                 *
                 * @param   AdministratorApplication  $app  Joomla administrator application.
                 *
                 * @since   3.0.0
                 */
                public function __construct(AdministratorApplication $app)
                {
                    $this->app = $app;
                    $this->db  = Factory::getContainer()->get(DatabaseDriver::class);
                }

                /**
                 * Accept a fresh package installation after preflight has passed.
                 *
                 * @param   InstallerAdapter  $adapter  Joomla installer adapter.
                 *
                 * @return  bool
                 *
                 * @since   3.0.0
                 */
                public function install(InstallerAdapter $adapter): bool
                {
                    return true;
                }

                /**
                 * Accept package uninstallation without deleting extra runtime data.
                 *
                 * @param   InstallerAdapter  $adapter  Joomla installer adapter.
                 *
                 * @return  bool
                 *
                 * @since   3.0.0
                 */
                public function uninstall(InstallerAdapter $adapter): bool
                {
                    return true;
                }

                /**
                 * Accept package update after preflight has passed.
                 *
                 * @param   InstallerAdapter  $adapter  Joomla installer adapter.
                 *
                 * @return  bool
                 *
                 * @since   3.0.0
                 */
                public function update(InstallerAdapter $adapter): bool
                {
                    return true;
                }

                /**
                 * Block installation or update when Joomla/PHP versions are below project requirements.
                 *
                 * @param   string            $type     Installer lifecycle type.
                 * @param   InstallerAdapter  $adapter  Joomla installer adapter.
                 *
                 * @return  bool
                 *
                 * @since   3.0.0
                 */
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

                /**
                 * Enable the configuration plugin and queue the branded WebTolk installer message.
                 *
                 * @param   string            $type     Installer lifecycle type.
                 * @param   InstallerAdapter  $adapter  Joomla installer adapter.
                 *
                 * @return  bool
                 *
                 * @since   3.0.0
                 */
                public function postflight(string $type, InstallerAdapter $adapter): bool
                {
                    if (in_array($type, ['install', 'discover_install', 'update'], true)) {
                        $this->enablePlugin('wt_otpravkapochtaru', 'system');
                    }

                    $html = $this->renderInstallationMessage($type, (string) $adapter->getManifest()->version);

                    $this->app->enqueueMessage($html, 'info');

                    return true;
                }

                /**
                 * Build the branded HTML message shown after install/update/uninstall.
                 *
                 * The method only returns markup; Joomla output is handled through enqueueMessage().
                 *
                 * @param   string  $type     Installer lifecycle type.
                 * @param   string  $version  Installed package version.
                 *
                 * @return  string
                 *
                 * @since   3.0.0
                 */
                protected function renderInstallationMessage(string $type, string $version): string
                {
                    $smile = '';

                    if ($type !== 'uninstall') {
                        $smiles = ['&#9786;', '&#128512;', '&#128521;', '&#128525;', '&#128526;', '&#128522;', '&#128591;'];
                        $smile  = $smiles[array_rand($smiles)];
                    }

                    $typeUpper = strtoupper($type);

                    return '
                    <div class="row m-0">
                        <div class="col-12 col-md-8 p-0 pe-2">
                            <h2>' . $smile . ' ' . Text::_('PKG_LIB_WT_OTPRAVKAPOCHTARU_AFTER_' . $typeUpper) . ' <br/>' . Text::_('PKG_LIB_WT_OTPRAVKAPOCHTARU') . '</h2>
                            ' . Text::_('PKG_LIB_WT_OTPRAVKAPOCHTARU_XML_DESCRIPTION') . '
                            ' . Text::sprintf('PKG_LIB_WT_OTPRAVKAPOCHTARU_WHATS_NEW', $version) . '
                        </div>
                        <div class="col-12 col-md-4 p-0 d-flex flex-column justify-content-start">
                            <img width="180" src="https://web-tolk.ru/web_tolk_logo_wide.png" alt="WebTolk">
                            <p>Joomla Extensions</p>
                            <p class="btn-group">
                                <a class="btn btn-sm btn-outline-primary" href="https://web-tolk.ru" target="_blank" rel="noopener noreferrer">https://web-tolk.ru</a>
                                <a class="btn btn-sm btn-outline-primary" href="mailto:info@web-tolk.ru"><i class="icon-envelope"></i> info@web-tolk.ru</a>
                            </p>
                            <div class="btn-group-vertical mb-3 web-tolk-btn-links" role="group" aria-label="WebTolk community links">
                                <a class="btn btn-danger text-white w-100" href="https://t.me/joomlaru" target="_blank" rel="noopener noreferrer">' . Text::_('PKG_LIB_WT_OTPRAVKAPOCHTARU_JOOMLARU_TELEGRAM_CHAT') . '</a>
                                <a class="btn btn-primary text-white w-100" href="https://t.me/webtolkru" target="_blank" rel="noopener noreferrer">' . Text::_('PKG_LIB_WT_OTPRAVKAPOCHTARU_WEBTOLK_TELEGRAM_CHANNEL') . '</a>
                                <a class="btn btn-success text-white w-100" href="https://max.ru/join/LChBfwGDmArJpK6--oS0qVAJA1WdRk0OPXciwryF4ZY" target="_blank" rel="noopener noreferrer">' . Text::_('PKG_LIB_WT_OTPRAVKAPOCHTARU_MAX_CHANNEL') . '</a>
                            </div>
                            ' . Text::_('PKG_LIB_WT_OTPRAVKAPOCHTARU_MAYBE_INTERESTING') . '
                        </div>
                    </div>';
                }

                /**
                 * Enable a plugin by element/folder in Joomla's extensions table.
                 *
                 * @param   string  $element  Plugin element.
                 * @param   string  $folder   Plugin folder.
                 *
                 * @return  void
                 *
                 * @since   3.0.0
                 */
                protected function enablePlugin(string $element, string $folder): void
                {
                    $plugin          = new stdClass();
                    $plugin->type    = 'plugin';
                    $plugin->element = $element;
                    $plugin->folder  = $folder;
                    $plugin->enabled = 1;

                    $this->db->updateObject('#__extensions', $plugin, ['type', 'element', 'folder']);
                }

                /**
                 * Verify the current Joomla version and enqueue a localized installer error if it is too old.
                 *
                 * @return  bool
                 *
                 * @since   3.0.0
                 */
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

                /**
                 * Verify the current PHP version and enqueue a localized installer error if it is too old.
                 *
                 * @return  bool
                 *
                 * @since   3.0.0
                 */
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
