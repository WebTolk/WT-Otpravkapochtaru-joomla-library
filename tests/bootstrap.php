<?php

defined('_JEXEC') || define('_JEXEC', 1);
defined('JPATH_SITE') || define('JPATH_SITE', 'D:/OSPanel/home/joomla.local/public');

spl_autoload_register(
    static function (string $class): void {
        $joomlaRoot = 'D:/.agents/docs/joomla/core/Joomla-core/6.x/6.1.0';
        $prefixes   = [
            'Webtolk\\Otpravkapochtaru\\'                   => __DIR__ . '/../lib_webtolk_otpravkapochtaru/src/',
            'Webtolk\\Plugin\\System\\WtOtpravkapochtaru\\' => __DIR__ . '/../plg_system_wt_otpravkapochtaru/src/',
            'Joomla\\CMS\\'                                 => $joomlaRoot . '/libraries/src/',
            'Joomla\\Application\\'                         => $joomlaRoot . '/libraries/vendor/joomla/application/src/',
            'Joomla\\Archive\\'                             => $joomlaRoot . '/libraries/vendor/joomla/archive/src/',
            'Joomla\\Authentication\\'                      => $joomlaRoot . '/libraries/vendor/joomla/authentication/src/',
            'Joomla\\Console\\'                             => $joomlaRoot . '/libraries/vendor/joomla/console/src/',
            'Joomla\\Crypt\\'                               => $joomlaRoot . '/libraries/vendor/joomla/crypt/src/',
            'Joomla\\Data\\'                                => $joomlaRoot . '/libraries/vendor/joomla/data/src/',
            'Joomla\\Database\\'                            => $joomlaRoot . '/libraries/vendor/joomla/database/src/',
            'Joomla\\DI\\'                                  => $joomlaRoot . '/libraries/vendor/joomla/di/src/',
            'Joomla\\Event\\'                               => $joomlaRoot . '/libraries/vendor/joomla/event/src/',
            'Joomla\\Filesystem\\'                          => $joomlaRoot . '/libraries/vendor/joomla/filesystem/src/',
            'Joomla\\Filter\\'                              => $joomlaRoot . '/libraries/vendor/joomla/filter/src/',
            'Joomla\\Http\\'                                => $joomlaRoot . '/libraries/vendor/joomla/http/src/',
            'Joomla\\Input\\'                               => $joomlaRoot . '/libraries/vendor/joomla/input/src/',
            'Joomla\\Language\\'                            => $joomlaRoot . '/libraries/vendor/joomla/language/src/',
            'Joomla\\OAuth1\\'                              => $joomlaRoot . '/libraries/vendor/joomla/oauth1/src/',
            'Joomla\\OAuth2\\'                              => $joomlaRoot . '/libraries/vendor/joomla/oauth2/src/',
            'Joomla\\Registry\\'                            => $joomlaRoot . '/libraries/vendor/joomla/registry/src/',
            'Joomla\\Router\\'                              => $joomlaRoot . '/libraries/vendor/joomla/router/src/',
            'Joomla\\Session\\'                             => $joomlaRoot . '/libraries/vendor/joomla/session/src/',
            'Joomla\\String\\'                              => $joomlaRoot . '/libraries/vendor/joomla/string/src/',
            'Joomla\\Uri\\'                                 => $joomlaRoot . '/libraries/vendor/joomla/uri/src/',
            'Joomla\\Utilities\\'                           => $joomlaRoot . '/libraries/vendor/joomla/utilities/src/',
            'Laminas\\Diactoros\\'                          => $joomlaRoot . '/libraries/vendor/laminas/laminas-diactoros/src/',
            'Psr\\Cache\\'                                  => $joomlaRoot . '/libraries/vendor/psr/cache/src/',
            'Psr\\Clock\\'                                  => $joomlaRoot . '/libraries/vendor/psr/clock/src/',
            'Psr\\Container\\'                              => $joomlaRoot . '/libraries/vendor/psr/container/src/',
            'Psr\\EventDispatcher\\'                        => $joomlaRoot . '/libraries/vendor/psr/event-dispatcher/src/',
            'Psr\\Http\\Client\\'                           => $joomlaRoot . '/libraries/vendor/psr/http-client/src/',
            'Psr\\Http\\Message\\'                          => $joomlaRoot . '/libraries/vendor/psr/http-message/src/',
            'Psr\\Http\\Server\\'                           => $joomlaRoot . '/libraries/vendor/psr/http-server-handler/src/',
            'Psr\\Link\\'                                   => $joomlaRoot . '/libraries/vendor/psr/link/src/',
            'Psr\\Log\\'                                    => $joomlaRoot . '/libraries/vendor/psr/log/src/',
        ];

        foreach ($prefixes as $prefix => $baseDir) {
            if (!str_starts_with($class, $prefix)) {
                continue;
            }

            $relativeClass = substr($class, strlen($prefix));
            $file          = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

            if (is_file($file)) {
                require_once $file;
            }
        }
    }
);
