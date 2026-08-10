<?php

namespace Webtolk\Tests\Unit\Fields;

use PHPUnit\Framework\TestCase;
use Webtolk\Otpravkapochtaru\Service\LinkedSelectOptionsService;

final class LinkedSelectOptionsServiceTest extends TestCase
{
    public function testFindShippingPointByPostofficeCode(): void
    {
        $service = new LinkedSelectOptionsService();

        self::assertSame(
            ['operator-postcode' => '222222'],
            $service->findShippingPoint(
                [
                    ['operator-postcode' => '111111'],
                    ['operator-postcode' => '222222'],
                ],
                '222222'
            )
        );
    }

    public function testMailTypeOptionsDeriveTypesFromProductsWhenPrimaryListIsAbsent(): void
    {
        $service = new LinkedSelectOptionsService();

        self::assertSame(
            [
                ['value' => 'A', 'text' => 'A'],
                ['value' => 'B', 'text' => 'B'],
                ['value' => 'Z', 'text' => 'Z'],
            ],
            $service->getMailTypeOptions(
                [
                    [
                        'operator-postcode'       => '111111',
                        'user-available-products' => [
                            ['mail-type' => 'Z'],
                            ['mail-type' => 'A'],
                            ['mail-type' => 'B'],
                        ],
                    ],
                ],
                '111111'
            )
        );
    }

    public function testMailTypeOptionsUsePrimaryMailTypesWhenAvailable(): void
    {
        $service = new LinkedSelectOptionsService();

        self::assertSame(
            [
                ['value' => 'EMS', 'text' => 'EMS'],
                ['value' => 'POSTAL_PARCEL', 'text' => 'POSTAL_PARCEL'],
            ],
            $service->getMailTypeOptions(
                [
                    [
                        'operator-postcode'         => '111111',
                        'user-available-mail-types' => [
                            'POSTAL_PARCEL',
                            'EMS',
                        ],
                        'user-available-products' => [
                            ['mail-type' => 'IGNORED_PRODUCT'],
                        ],
                    ],
                ],
                '111111'
            )
        );
    }

    public function testMailCategoryOptionsByTypeAreSortedAndFiltered(): void
    {
        $service = new LinkedSelectOptionsService();

        self::assertSame(
            [
                ['value' => 'A', 'text' => 'A'],
                ['value' => 'B', 'text' => 'B'],
                ['value' => 'C', 'text' => 'C'],
            ],
            $service->getMailCategoryOptions(
                [
                    [
                        'operator-postcode'       => '111111',
                        'user-available-products' => [
                            ['mail-type' => 'PARCEL', 'mail-category' => 'B'],
                            ['mail-type' => 'PARCEL', 'mail-category' => 'A'],
                            ['mail-type' => 'PARCEL', 'mail-category' => 'C'],
                            ['mail-type' => 'OTHER', 'mail-category' => 'Z'],
                        ],
                    ],
                ],
                '111111',
                'PARCEL'
            )
        );
    }
}
