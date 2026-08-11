<?php

namespace Webtolk\Tests\Unit\Sdk;

use LapayGroup\RussianPost\Entity\Order;
use LapayGroup\RussianPost\Providers\Calculation;
use LapayGroup\RussianPost\Providers\OtpravkaApi;
use PHPUnit\Framework\TestCase;

final class UpstreamSdkContractTest extends TestCase
{
    public function testReleaseVendorAutoloadExposesUpstreamSdkClasses(): void
    {
        self::assertTrue(
            class_exists(Order::class),
            'Release vendor autoload must expose the upstream SDK Order entity.'
        );
        self::assertTrue(
            class_exists(OtpravkaApi::class),
            'Release vendor autoload must expose the upstream SDK Otpravka API provider.'
        );
        self::assertTrue(
            class_exists(Calculation::class),
            'Release vendor autoload must expose the upstream SDK tariff provider.'
        );
    }
}
