<?php

namespace Webtolk\Tests\Unit\Entity;

use PHPUnit\Framework\TestCase;
use Webtolk\Otpravkapochtaru\Entity\Order;
use Webtolk\Otpravkapochtaru\Exception\ValidationException;

final class OrderTest extends TestCase
{
    public function testFromArrayNormalizesKeysAndDefaults(): void
    {
        $order = Order::fromArray([
            'indexTo'       => '455001',
            'recipientName' => 'Test Recipient',
            'goods'         => [
                'items' => [
                    ['description' => 'Item A'],
                ],
            ],
        ]);

        self::assertSame(
            [
                'index-to'        => '455001',
                'recipient-name'  => 'Test Recipient',
                'address-type-to' => 'DEFAULT',
                'fragile'         => false,
                'mail-category'   => 'ORDINARY',
                'mail-direct'     => 643,
                'mail-type'       => 'POSTAL_PARCEL',
                'goods'           => [
                    'items' => [
                        [
                            'description' => 'Item A',
                            'quantity'    => 1,
                            'value'       => 0,
                            'vat-rate'    => -1,
                        ],
                    ],
                ],
            ],
            $order->toArray()
        );
    }

    public function testToArrayRequiresDestinationIndex(): void
    {
        $this->expectException(ValidationException::class);

        Order::fromArray([
            'recipientName' => 'Test Recipient',
        ])->toArray();
    }
}
