<?php

declare(strict_types=1);

namespace Webtolk\Tests\Unit\Facade;

use LapayGroup\RussianPost\Http\Psr18Transport;
use LapayGroup\RussianPost\Providers\Calculation;
use LapayGroup\RussianPost\Providers\OtpravkaApi;
use LapayGroup\RussianPost\Providers\Tracking;
use PHPUnit\Framework\TestCase;
use Webtolk\Otpravkapochtaru\Joomla\CredentialsProvider;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

final class ThinFacadeContractTest extends TestCase
{
    public function testFacadePublicSurfaceStaysThin(): void
    {
        $methods = [];
        $class   = new \ReflectionClass(Otpravkapochtaru::class);

        foreach ($class->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->getDeclaringClass()->getName() !== Otpravkapochtaru::class) {
                continue;
            }

            $methods[] = $method->getName();
        }

        sort($methods);

        self::assertSame(
            [
                '__construct',
                'calculation',
                'credentialsProvider',
                'getAccountInfo',
                'getApiLimit',
                'otpravkaApi',
                'trackingApi',
                'transport',
            ],
            $methods
        );
    }

    public function testFacadeReturnTypesExposeConfiguredSdkProviders(): void
    {
        $class = new \ReflectionClass(Otpravkapochtaru::class);

        self::assertSame(CredentialsProvider::class, (string) $class->getMethod('credentialsProvider')->getReturnType());
        self::assertSame(Psr18Transport::class, (string) $class->getMethod('transport')->getReturnType());
        self::assertSame(OtpravkaApi::class, (string) $class->getMethod('otpravkaApi')->getReturnType());
        self::assertSame(Calculation::class, (string) $class->getMethod('calculation')->getReturnType());
        self::assertSame(Tracking::class, (string) $class->getMethod('trackingApi')->getReturnType());
    }

    public function testFacadeDoesNotExposeDomainOperationAliases(): void
    {
        $class = new \ReflectionClass(Otpravkapochtaru::class);

        foreach (
            [
                'createOrders',
                'editOrder',
                'findOrderById',
                'getTariff',
                'getTariffAndDeliveryPeriod',
                'getShippingPoints',
                'searchPostOfficeByIndex',
                'getOperationsByRpo',
            ] as $method
        ) {
            self::assertFalse($class->hasMethod($method), sprintf('Facade must not expose %s().', $method));
        }
    }
}
