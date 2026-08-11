<?php

namespace Webtolk\Tests\Unit\Facade;

use LapayGroup\RussianPost\Entity\Item;
use LapayGroup\RussianPost\Entity\Order;
use PHPUnit\Framework\TestCase;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

final class OrderPayloadNormalizationTest extends TestCase
{
    public function testFacadeHydratesUpstreamOrderFromLegacyArrayPayload(): void
    {
        $facade = (new \ReflectionClass(Otpravkapochtaru::class))->newInstanceWithoutConstructor();
        $method = new \ReflectionMethod(Otpravkapochtaru::class, 'normalizeUpstreamOrder');
        $method->setAccessible(true);

        $order = $method->invoke(
            $facade,
            [
                'indexTo'       => '455001',
                'recipientName' => 'Test Recipient',
                'mailType'      => 'POSTAL_PARCEL',
                'mailCategory'  => 'ORDINARY',
                'mass'          => 1000,
                'goods'         => [
                    'items' => [
                        ['description' => 'Item A'],
                    ],
                ],
            ]
        );

        self::assertInstanceOf(Order::class, $order);
        self::assertSame('455001', $order->getIndexTo());
        self::assertSame('Test Recipient', $order->getRecipientName());
        self::assertSame('POSTAL_PARCEL', $order->getMailType());
        self::assertSame('ORDINARY', $order->getMailCategory());
        self::assertSame(1000, $order->getMass());

        $items = $order->getItems();
        self::assertIsArray($items);
        self::assertCount(1, $items);
        self::assertInstanceOf(Item::class, $items[0]);
        self::assertSame('Item A', $items[0]->getDescription());
        self::assertSame(1, $items[0]->getQuantity());
        self::assertSame(0, $items[0]->getValue());
        self::assertSame(-1, $items[0]->getVatRate());
    }

    public function testFacadeRejectsInvalidOrderPayload(): void
    {
        $facade = (new \ReflectionClass(Otpravkapochtaru::class))->newInstanceWithoutConstructor();
        $method = new \ReflectionMethod(Otpravkapochtaru::class, 'normalizeUpstreamOrder');
        $method->setAccessible(true);

        $this->expectException(\InvalidArgumentException::class);

        $method->invoke($facade, new \stdClass());
    }
}
