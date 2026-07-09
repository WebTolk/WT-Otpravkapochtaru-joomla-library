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

                    $html = $this->renderInstallationMessage($type, (string) $adapter->getManifest()->version);

                    $this->app->enqueueMessage($html, 'info');

                    return true;
                }

                protected function renderInstallationMessage(string $type, string $version): string
                {
                    $actionText = match ($type) {
                        'install', 'discover_install' => Text::_('PKG_LIB_WT_OTPRAVKAPOCHTARU_BRAND_ACTION_INSTALLED'),
                        'update'                      => Text::_('PKG_LIB_WT_OTPRAVKAPOCHTARU_BRAND_ACTION_UPDATED'),
                        'uninstall'                   => Text::_('PKG_LIB_WT_OTPRAVKAPOCHTARU_BRAND_ACTION_UNINSTALLED'),
                        default                       => Text::_('PKG_LIB_WT_OTPRAVKAPOCHTARU_BRAND_ACTION_PROCESSED'),
                    };

                    $html   = [];
                    $html[] = '<div class="wt-install-message card shadow-sm border-0 my-3">';
                    $html[] = '<div class="card-body">';
                    $html[] = '<div class="d-flex flex-column flex-md-row gap-3 align-items-start">';
                    $html[] = '<div class="flex-shrink-0">';
                    $html[] = '<a href="https://web-tolk.ru" target="_blank" rel="noopener noreferrer" aria-label="WebTolk">';
                    $html[] = $this->renderWebtolkLogo();
                    $html[] = '</a>';
                    $html[] = '</div>';
                    $html[] = '<div class="flex-grow-1">';
                    $html[] = '<div class="d-flex flex-wrap gap-2 align-items-center mb-2">';
                    $html[] = '<span class="badge bg-success">v' . $version . '</span>';
                    $html[] = '<span class="badge bg-info text-dark">' . $actionText . '</span>';
                    $html[] = '</div>';
                    $html[] = '<h3 class="h5 mb-2">' . Text::_('PKG_LIB_WT_OTPRAVKAPOCHTARU_BRAND_TITLE') . '</h3>';
                    $html[] = '<p class="mb-3">' . Text::_('PKG_LIB_WT_OTPRAVKAPOCHTARU_BRAND_DESCRIPTION') . '</p>';
                    $html[] = '<ul class="mb-3 ps-3">';
                    $html[] = '<li>' . Text::_('PKG_LIB_WT_OTPRAVKAPOCHTARU_BRAND_FEATURE_SETTINGS') . '</li>';
                    $html[] = '<li>' . Text::_('PKG_LIB_WT_OTPRAVKAPOCHTARU_BRAND_FEATURE_API') . '</li>';
                    $html[] = '<li>' . Text::_('PKG_LIB_WT_OTPRAVKAPOCHTARU_BRAND_FEATURE_TRACKING') . '</li>';
                    $html[] = '</ul>';
                    $html[] = '<div class="d-flex flex-wrap gap-2">';
                    $html[] = '<a class="btn btn-primary btn-sm" href="index.php?option=com_plugins&view=plugins&filter[folder]=system&filter[element]=wt_otpravkapochtaru">';
                    $html[] = Text::_('PKG_LIB_WT_OTPRAVKAPOCHTARU_BRAND_OPEN_PLUGIN');
                    $html[] = '</a>';
                    $html[] = '<a class="btn btn-outline-secondary btn-sm" href="https://github.com/WebTolk/WT-Otpravkapochtaru-joomla-library" target="_blank" rel="noopener noreferrer">';
                    $html[] = Text::_('PKG_LIB_WT_OTPRAVKAPOCHTARU_BRAND_GITHUB');
                    $html[] = '</a>';
                    $html[] = '<a class="btn btn-outline-secondary btn-sm" href="https://web-tolk.ru" target="_blank" rel="noopener noreferrer">';
                    $html[] = Text::_('PKG_LIB_WT_OTPRAVKAPOCHTARU_BRAND_WEBTOLK');
                    $html[] = '</a>';
                    $html[] = '</div>';
                    $html[] = '</div>';
                    $html[] = '</div>';
                    $html[] = '</div>';
                    $html[] = '</div>';

                    return implode('', $html);
                }

                protected function renderWebtolkLogo(): string
                {
                    return '<svg width="200" height="50" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="WebTolk">'
                        . '<text font-weight="bold" text-anchor="start" font-family="Helvetica, Arial, sans-serif" font-size="32" y="36" x="8" fill="#0fa2e6">Web</text>'
                        . '<text font-weight="bold" text-anchor="start" font-family="Helvetica, Arial, sans-serif" font-size="32" y="36" x="74" fill="#384148">Tolk</text>'
                        . '</svg>';
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
