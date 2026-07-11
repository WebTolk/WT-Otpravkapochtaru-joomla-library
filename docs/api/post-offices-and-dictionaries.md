# Отделения и локальные справочники

Методы отделений используют раздел `/postoffice` основного REST API. Они помогают проверить индекс, подобрать ближайшее отделение и определить перечень услуг до создания отправления.

<a id="getcountrylist"></a>
## `getCountryList(): array`

**Что делает.** Возвращает встроенный справочник стран.

**Зачем нужен.** Справочник позволяет заполнить поле направления и код страны без сетевого запроса и расходования лимита.

**Как работает.** Вызывает `CountryDictionary::all()`; внешний API не используется.

| Результат | Тип | Содержание |
| --- | --- | --- |
| Возвращаемое значение | `array<int, string>` | Код страны и русское название. |

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

$client = new Otpravkapochtaru();
$countries = $client->getCountryList();

var_dump([
    'russia' => $countries[643] ?? null,
    'belarus' => $countries[112] ?? null,
    'total' => count($countries),
]);
```

<a id="searchpostofficebyindex"></a>
## `searchPostOfficeByIndex(int|string $postalCode, ?string $latitude = null, ?string $longitude = null, ?string $currentDateTime = null, bool $filterByOfficeType = true, bool $ufpsPostalCode = false): array`

**Что делает.** Получает сведения об отделении по почтовому индексу.

**Зачем нужен.** Метод проверяет индекс и возвращает адрес, координаты, расписание, телефоны и группы услуг.

**Как работает.** Выполняет `GET /postoffice/1.0/{postalCode}`. Пустые координаты исключаются, логические параметры преобразуются в строки `true`/`false`.

| Параметр | Тип | По умолчанию | Назначение |
| --- | --- | --- | --- |
| `$postalCode` | `int\|string` | — | Шестизначный индекс. |
| `$latitude` | `string\|null` | `null` | Широта для уточнения. |
| `$longitude` | `string\|null` | `null` | Долгота для уточнения. |
| `$currentDateTime` | `string\|null` | `null` | Дата и время для актуального расписания. |
| `$filterByOfficeType` | `bool` | `true` | Учитывать тип отделения. |
| `$ufpsPostalCode` | `bool` | `false` | Интерпретировать индекс как индекс УФПС. |

Наблюдаемые данные: [Саратов, 410000](../api-schemas/otpravka/examples/search-post-office-by-index-from.response.json), [схема](../api-schemas/otpravka/schemas/search-post-office-by-index-from.response.schema.json), [Магадан, 685000](../api-schemas/otpravka/examples/search-post-office-by-index-to.response.json).

```php
<?php

declare(strict_types=1);

use Joomla\CMS\Factory;
use Webtolk\Otpravkapochtaru\Exception\OtpravkapochtaruException;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

$client = new Otpravkapochtaru();
$postalCode = Factory::getApplication()->getInput()->getString('postal_code', '685000');

try {
    $office = $client->searchPostOfficeByIndex(
        postalCode: $postalCode,
        currentDateTime: date(DATE_ATOM),
        filterByOfficeType: true,
    );

    var_dump([
        'postal_code' => $office['postal-code'] ?? null,
        'address' => $office['address-source'] ?? null,
        'coordinates' => [$office['latitude'] ?? null, $office['longitude'] ?? null],
        'working_hours' => $office['working-hours'] ?? [],
    ]);
} catch (OtpravkapochtaruException $exception) {
    throw new RuntimeException('Не удалось получить отделение по индексу.', 0, $exception);
}
```

<a id="searchpostofficebyaddress"></a>
## `searchPostOfficeByAddress(string $address, int $count = 3): array`

**Что делает.** Подбирает почтовые индексы по свободно записанному адресу.

**Зачем нужен.** Метод полезен, когда пользователь знает город или улицу, но не знает индекс.

**Как работает.** Выполняет `GET /postoffice/1.0/by-address` с параметрами `address` и `top`. Наблюдаемый ответ содержит `is-matched` и список `postoffices`.

Наблюдаемые данные: [пример](../api-schemas/otpravka/examples/search-post-office-by-address.response.json), [JSON Schema](../api-schemas/otpravka/schemas/search-post-office-by-address.response.schema.json).

```php
<?php

declare(strict_types=1);

use Joomla\CMS\Factory;
use Webtolk\Otpravkapochtaru\Exception\OtpravkapochtaruException;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

$client = new Otpravkapochtaru();
$address = Factory::getApplication()->getInput()->getString('address', 'Магадан');

try {
    $result = $client->searchPostOfficeByAddress($address, 5);

    var_dump([
        'exact_match' => $result['is-matched'] ?? false,
        'postal_codes' => $result['postoffices'] ?? [],
    ]);
} catch (OtpravkapochtaruException $exception) {
    throw new RuntimeException('Не удалось подобрать отделения по адресу.', 0, $exception);
}
```

<a id="searchpostofficebycoordinates"></a>
## `searchPostOfficeByCoordinates(array $params): array`

**Что делает.** Ищет ближайшие отделения по координатам.

**Зачем нужен.** Метод применяется в карте пунктов обслуживания или при автоматическом подборе ближайшего отделения.

**Как работает.** Выполняет `GET /postoffice/1.0/nearby`. Если `filter` не передан, библиотека добавляет `filter=ALL`.

| Ключ параметров | Тип | Назначение |
| --- | --- | --- |
| `latitude` | `float\|string` | Широта. |
| `longitude` | `float\|string` | Долгота. |
| `top` | `int` | Максимальное количество результатов. |
| `filter` | `string` | Фильтр типов отделений; по умолчанию `ALL`. |

Наблюдаемые данные: [пример](../api-schemas/otpravka/examples/search-post-office-by-coordinates.response.json), [JSON Schema](../api-schemas/otpravka/schemas/search-post-office-by-coordinates.response.schema.json).

```php
<?php

declare(strict_types=1);

use Joomla\CMS\Factory;
use Webtolk\Otpravkapochtaru\Exception\OtpravkapochtaruException;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

$client = new Otpravkapochtaru();
$input = Factory::getApplication()->getInput();

try {
    $offices = $client->searchPostOfficeByCoordinates([
        'latitude' => $input->getFloat('latitude', 59.568176),
        'longitude' => $input->getFloat('longitude', 150.808528),
        'top' => $input->getInt('top', 10),
        'filter' => 'ALL',
    ]);

    foreach ($offices as $office) {
        var_dump([
            'postal_code' => $office['postal-code'] ?? null,
            'address' => $office['address-source'] ?? null,
            'distance' => $office['distance'] ?? null,
        ]);
    }
} catch (OtpravkapochtaruException $exception) {
    throw new RuntimeException('Не удалось найти ближайшие отделения.', 0, $exception);
}
```

<a id="getpostofficeservices"></a>
## `getPostOfficeServices(int|string $postalCode, ?string $serviceGroup = null): array`

**Что делает.** Возвращает услуги выбранного отделения.

**Зачем нужен.** Проверка позволяет заранее убедиться, что отделение выполняет требуемую операцию.

**Как работает.** Без группы выполняется `GET /postoffice/1.0/{postalCode}/services`; с группой её значение добавляется последним сегментом пути.

Наблюдаемые данные: [пример](../api-schemas/otpravka/examples/get-post-office-services.response.json), [JSON Schema](../api-schemas/otpravka/schemas/get-post-office-services.response.schema.json).

```php
<?php

declare(strict_types=1);

use Joomla\CMS\Factory;
use Webtolk\Otpravkapochtaru\Exception\OtpravkapochtaruException;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

$client = new Otpravkapochtaru();
$postalCode = Factory::getApplication()->getInput()->getString('postal_code', '685000');

try {
    $services = $client->getPostOfficeServices($postalCode);

    foreach ($services as $service) {
        var_dump([
            'code' => $service['code'] ?? null,
            'name' => $service['name'] ?? null,
            'group_id' => $service['group-id'] ?? null,
        ]);
    }
} catch (OtpravkapochtaruException $exception) {
    throw new RuntimeException('Не удалось получить услуги отделения.', 0, $exception);
}
```

<a id="getpostalcodesinlocality"></a>
## `getPostalCodesInLocality(string $locality, string $region = '', string $district = ''): array`

**Что делает.** Возвращает индексы населённого пункта.

**Зачем нужен.** Метод помогает построить подсказки и проверить индекс при неполном адресе.

**Как работает.** Выполняет `GET /postoffice/1.0/settlement.offices.codes` с параметрами `settlement`, `region` и `district`.

Наблюдаемые данные: [пример](../api-schemas/otpravka/examples/get-postal-codes-in-locality.response.json), [JSON Schema](../api-schemas/otpravka/schemas/get-postal-codes-in-locality.response.schema.json).

```php
<?php

declare(strict_types=1);

use Joomla\CMS\Factory;
use Webtolk\Otpravkapochtaru\Exception\OtpravkapochtaruException;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

$client = new Otpravkapochtaru();
$input = Factory::getApplication()->getInput();

try {
    $codes = $client->getPostalCodesInLocality(
        locality: $input->getString('locality', 'Магадан'),
        region: $input->getString('region', 'Магаданская область'),
        district: $input->getString('district', ''),
    );

    var_dump($codes);
} catch (OtpravkapochtaruException $exception) {
    throw new RuntimeException('Не удалось получить индексы населённого пункта.', 0, $exception);
}
```
