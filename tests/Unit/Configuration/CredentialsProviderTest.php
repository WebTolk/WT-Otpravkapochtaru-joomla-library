<?php

namespace Webtolk\Tests\Unit\Configuration;

use PHPUnit\Framework\TestCase;
use Webtolk\Otpravkapochtaru\Joomla\CredentialsProvider;

final class CredentialsProviderTest extends TestCase
{
    public function testReadsCanonicalCredentials(): void
    {
        $provider = new CredentialsProvider(
            [
                'access_token'      => 'access',
                'auth_mode'         => 'key',
                'user_key'          => 'user-key',
                'user_login'        => 'login',
                'user_password'     => 'password',
                'tracking_login'    => 'tracking-login',
                'tracking_password' => 'tracking-password',
                'http_timeout'      => 15,
            ]
        );

        self::assertSame('access', $provider->getAccessToken());
        self::assertSame('key', $provider->getAuthMode());
        self::assertSame('user-key', $provider->getUserKey());
        self::assertSame('user-key', $provider->getUserAuthorizationHeader());
        self::assertSame('login', $provider->getUserLogin());
        self::assertSame('password', $provider->getUserPassword());
        self::assertSame('tracking-login', $provider->getTrackingLogin());
        self::assertSame('tracking-password', $provider->getTrackingPassword());
        self::assertSame(15, $provider->getHttpTimeout());
    }

    public function testReadsExistingSystemPluginParameterNames(): void
    {
        $provider = new CredentialsProvider(
            [
                'AccessToken'                    => 'access',
                'user_key_or_login_and_password' => 'key',
                'user_auth_key'                  => 'user-key',
            ]
        );

        self::assertSame('access', $provider->getAccessToken());
        self::assertSame('key', $provider->getAuthMode());
        self::assertSame('user-key', $provider->getUserKey());
        self::assertSame('user-key', $provider->getUserAuthorizationHeader());
    }

    public function testCanonicalParameterNamesTakePriorityOverExistingSystemPluginNames(): void
    {
        $provider = new CredentialsProvider(
            [
                'access_token'                   => 'new-access',
                'AccessToken'                    => 'old-access',
                'auth_mode'                      => 'key',
                'user_key_or_login_and_password' => 'login_and_password',
                'user_key'                       => 'new-user-key',
                'user_auth_key'                  => 'old-user-key',
            ]
        );

        self::assertSame('new-access', $provider->getAccessToken());
        self::assertSame('key', $provider->getAuthMode());
        self::assertSame('new-user-key', $provider->getUserKey());
        self::assertSame('new-user-key', $provider->getUserAuthorizationHeader());
    }
}
