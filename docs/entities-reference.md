# Сущности данных

Сущности преобразуют удобные PHP-массивы в форму REST API. Имена ключей `camelCase` и `snake_case` рекурсивно переводятся в `kebab-case`, значения `null` удаляются, а обязательные поля проверяются до сетевого запроса.

Все конкретные сущности создаются фабрикой `fromArray()`: их конструкторы закрыты. Метод `toArray()` объявлен в `AbstractEntity` и реализован каждой сущностью.

## Общий контракт

### `AbstractEntity::toArray(): array`

**Что делает.** Преобразует сущность в ассоциативный массив, готовый для API.

**Зачем нужен.** Фасад использует этот метод перед отправкой, а прикладной код может применять его для журнала или предварительного просмотра.

**Как работает.** Точная проверка и вложенные разделы зависят от конкретной сущности.

Возвращаемый тип: `array<string, mixed>`. Непосредственно создать `AbstractEntity` нельзя.

## `Order`

### `Order::fromArray(array $data): Order`

Создаёт заказ, нормализует ключи, преобразует `goods.items`, `customs-declaration` и `ecom-data` во вложенные сущности. Добавляет значения по умолчанию:

| Поле | Значение |
| --- | --- |
| `address-type-to` | `DEFAULT` |
| `fragile` | `false` |
| `mail-category` | `ORDINARY` |
| `mail-direct` | `643` |
| `mail-type` | `POSTAL_PARCEL` |

### `Order::toArray(): array`

Проверяет наличие `index-to` или `str-index-to`, удаляет `null` и собирает вложенные разделы. При отсутствии индекса выбрасывает `ValidationException`.

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Entity\Order;

$order = Order::fromArray([
    'orderNum' => 'joomla-1042',
    'recipient_name' => 'Иванов Иван Иванович',
    'tel-address' => '+79000000000',
    'indexTo' => '685000',
    'mass' => 1000,
    'items' => [
        ['description' => 'Книга', 'quantity' => 1, 'value' => 150000],
    ],
]);

$payload = $order->toArray();

var_dump($payload);
```

Результат имеет тип `array<string, mixed>` и содержит как минимум нормализованные ключи и значения по умолчанию.

## `Recipient`

### `Recipient::fromArray(array $data): Recipient`

Принимает либо точные поля `raw-address`, `raw-full-name`, `raw-telephone`, либо короткие псевдонимы `address`, `name`, `phone`.

### `Recipient::toArray(): array`

Удаляет отсутствующие значения и возвращает массив для проверки надёжности. Обязательность полей внутри сущности не проверяется, поэтому прикладной код должен передать все доступные данные.

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Entity\Recipient;

$recipient = Recipient::fromArray([
    'address' => '685000, Магадан, проспект Ленина, дом 1',
    'name' => 'Иванов Иван Иванович',
    'phone' => '+79000000000',
]);

var_dump($recipient->toArray());
```

Схема результата:

```php
array{
    'raw-address'?: string,
    'raw-full-name'?: string,
    'raw-telephone'?: string
}
```

## `ReturnShipment`

### `ReturnShipment::fromArray(array $data): ReturnShipment`

Нормализует основные поля и преобразует `address-from`/`address-to` в `AddressReturn`.

### `ReturnShipment::toArray(): array`

Требует непустые `mail-type`, `recipient-name`, `sender-name` и обязательный `address-from`. Адрес получателя возврата необязателен.

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Entity\ReturnShipment;

$returnShipment = ReturnShipment::fromArray([
    'mailType' => 'POSTAL_PARCEL',
    'recipientName' => 'Получатель возврата',
    'senderName' => 'Отправитель возврата',
    'addressFrom' => [
        'index' => '685000',
        'region' => 'Магаданская область',
        'place' => 'Магадан',
    ],
    'addressTo' => [
        'index' => '410000',
        'region' => 'Саратовская область',
        'place' => 'Саратов',
    ],
]);

var_dump($returnShipment->toArray());
```

## `AddressReturn`

### `AddressReturn::fromArray(array $data): AddressReturn`

Нормализует ключи и добавляет `address-type=DEFAULT`, если тип не передан.

### `AddressReturn::toArray(): array`

Требует `index`, `place` и `region`; прочие адресные поля необязательны.

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Entity\AddressReturn;

$address = AddressReturn::fromArray([
    'index' => '410000',
    'region' => 'Саратовская область',
    'place' => 'Саратов',
    'street' => 'Московская',
    'house' => '1',
]);

var_dump($address->toArray());
```

Схема результата:

```php
array{
    index: string|int,
    region: string,
    place: string,
    address-type: string,
    street?: string,
    house?: string,
    ...
}
```

## `Item`

### `Item::fromArray(array $data): Item`

Создаёт товарную позицию внутри `goods.items`. По умолчанию устанавливает `quantity=1`, `value=0`, `vat-rate=-1`.

### `Item::toArray(): array`

Возвращает поля позиции без `null`.

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Entity\Item;

$item = Item::fromArray([
    'description' => 'Книга',
    'quantity' => 2,
    'value' => 150000,
    'vatRate' => -1,
]);

var_dump($item->toArray());
```

Денежное поле `value` передаётся в минимальных денежных единицах, принятых контрактом API.

## `EcomData`

### `EcomData::fromArray(array $data): EcomData`

Создаёт раздел электронной торговли. По умолчанию добавляет `delivery-rate=0` и `delivery-vat-rate=-1`.

### `EcomData::toArray(): array`

Удаляет `null` и возвращает раздел `ecom-data`.

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Entity\EcomData;

$ecom = EcomData::fromArray([
    'deliveryRate' => 45000,
    'deliveryVatRate' => 20,
    'value' => 150000,
]);

var_dump($ecom->toArray());
```

## `CustomsDeclaration`

### `CustomsDeclaration::fromArray(array $data): CustomsDeclaration`

Создаёт таможенную декларацию и преобразует `customs-entries` в `CustomsDeclarationItem`. По умолчанию устанавливает `currency=RUB` и `entries-type=GIFT`.

### `CustomsDeclaration::toArray(): array`

Возвращает атрибуты декларации и список преобразованных позиций.

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Entity\CustomsDeclaration;

$declaration = CustomsDeclaration::fromArray([
    'currency' => 'RUB',
    'entriesType' => 'SALE_OF_GOODS',
    'customsEntries' => [
        [
            'description' => 'Книга',
            'amount' => 150000,
            'weight' => 700,
            'countryCode' => 643,
        ],
    ],
]);

var_dump($declaration->toArray());
```

## `CustomsDeclarationItem`

### `CustomsDeclarationItem::fromArray(array $data): CustomsDeclarationItem`

Создаёт одну строку декларации. Добавляет значения `amount=0`, `country-code=643`, пустые `description`, `tnved-code`, `trademark` и `weight=0`, если они отсутствуют.

### `CustomsDeclarationItem::toArray(): array`

Возвращает нормализованную строку декларации без `null`.

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Entity\CustomsDeclarationItem;

$item = CustomsDeclarationItem::fromArray([
    'description' => 'Печатная книга',
    'amount' => 150000,
    'weight' => 700,
    'countryCode' => 643,
    'tnvedCode' => '4901990000',
]);

var_dump($item->toArray());
```

## Вложенные сущности в заказе

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Entity\CustomsDeclaration;
use Webtolk\Otpravkapochtaru\Entity\EcomData;
use Webtolk\Otpravkapochtaru\Entity\Item;
use Webtolk\Otpravkapochtaru\Entity\Order;

$order = Order::fromArray([
    'order-num' => 'joomla-1042',
    'recipient-name' => 'Иванов Иван Иванович',
    'tel-address' => '+79000000000',
    'index-to' => '685000',
    'mass' => 1000,
    'items' => [
        Item::fromArray([
            'description' => 'Книга',
            'quantity' => 1,
            'value' => 150000,
        ]),
    ],
    'ecom-data' => EcomData::fromArray([
        'delivery-rate' => 45000,
        'delivery-vat-rate' => 20,
    ]),
    'customs-declaration' => CustomsDeclaration::fromArray([
        'customs-entries' => [
            ['description' => 'Книга', 'amount' => 150000, 'weight' => 700],
        ],
    ]),
]);

var_dump($order->toArray());
```
