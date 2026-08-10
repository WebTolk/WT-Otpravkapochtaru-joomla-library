<?php

/**
 * Joomla service provider for the system plugin configuration surface.
 *
 * @package     WT Otpravkapochtaru
 * @version     3.0.0
 * @author      Sergey Tolkachyov
 * @copyright   Copyright (c) 2026 Sergey Tolkachyov, WebTolk. All rights reserved.
 * @license     GNU/GPL 3.0
 * @since       0.1.0
 */

defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Webtolk\Plugin\System\WtOtpravkapochtaru\Extension\WtOtpravkapochtaru;

return new class () implements ServiceProviderInterface {
    /**
     * Register the plugin extension class in Joomla's dependency injection container.
     *
     * @since 3.0.0
     */
    public function register(Container $container): void
    {
        $container->set(
            PluginInterface::class,
            function (Container $container) {
                $dispatcher = $container->get(DispatcherInterface::class);
                $plugin     = new WtOtpravkapochtaru(
                    $dispatcher,
                    (array) PluginHelper::getPlugin('system', 'wtotpravkapochtaru')
                );
                $plugin->setApplication(Factory::getApplication());

                return $plugin;
            }
        );
    }
};
