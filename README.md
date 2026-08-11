# WT Otpravkapochtaru Joomla Library

Пакет Joomla 5+ для работы с отправкой и отслеживанием Почты России.

## Архитектура

- Пакет является Joomla-оберткой над upstream SDK `lapaygroup/russianpost`.
- Основная точка входа для прикладного кода: `Webtolk\Otpravkapochtaru\Otpravkapochtaru`.
- GitHub/Composer-сборка подтягивает upstream SDK и при упаковке кладет его в:
  - `lib_webtolk_otpravkapochtaru/src/libraries/vendor/lapaygroup/russianpost/src`;
  - `lib_webtolk_otpravkapochtaru/src/libraries/vendor/autoload.php`.
- Joomla Form fields и web assets принадлежат библиотеке:
  - `lib_webtolk_otpravkapochtaru/src/Fields`;
  - `lib_webtolk_otpravkapochtaru/media/joomla.asset.json`;
  - `lib_webtolk_otpravkapochtaru/media/js/linked-select-fields.js`.

## Устанавливаемые расширения

- `WT Otpravkapochtaru library` - PHP-библиотека с пространством имен `Webtolk\Otpravkapochtaru`.
- `System - WT Otpravkapochtaru` - системный плагин Joomla для хранения настроек API.

## Требования

- Joomla 5 или новее.
- PHP 8.3.0 или новее.
- PHP extension `mbstring`.
- PHP extension `soap` нужен только для SOAP-трекинга; отсутствие SOAP не блокирует установку Joomla-пакета, но установщик показывает предупреждение.
- Для GitHub/Composer-сборки также требуются `ext-soap` и `ext-zip`.

## Установка и настройка

1. Установите ZIP-пакет через `System -> Install -> Extensions`.
2. Откройте `System -> Plugins`.
3. Найдите `System - WT Otpravkapochtaru`.
4. Заполните `AccessToken`.
5. Выберите режим авторизации пользователя.
6. Укажите `user_auth_key` либо пару `user_login` / `user_password`.
7. Для SOAP-трекинга заполните `tracking_login` и `tracking_password`.
8. Включите плагин и сохраните настройки.

## Быстрый старт

Пример должен выполняться внутри загруженного приложения Joomla после установки пакета и настройки системного плагина.

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

$client = new Otpravkapochtaru();

$delivery = $client->getTariffAndDeliveryPeriod(
    objectId: 27030,
    params: [
        'mail-type' => 'POSTAL_PARCEL',
        'mail-category' => 'ORDINARY',
        'index-from' => '410000',
        'index-to' => '685000',
        'mass' => 1000,
    ],
);

var_dump($delivery);
```

Создание заказа выполняется через фасад. Можно передавать массивы payload, которые фасад преобразует в upstream-сущности `LapayGroup\RussianPost`.

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

$client = new Otpravkapochtaru();

$result = $client->createOrders([
    [
        'order-num' => 'joomla-' . date('Ymd-His'),
        'recipient-name' => 'Ivanov Ivan',
        'tel-address' => '79000000000',
        'index-to' => '685000',
        'region-to' => 'Magadan region',
        'place-to' => 'Magadan',
        'street-to' => 'Lenina',
        'house-to' => '1',
        'mail-type' => 'POSTAL_PARCEL',
        'mail-category' => 'ORDINARY',
        'mass' => 1000,
    ],
]);
```

## Документация

- [Техническая документация](docs/developer-api.md).
- [Методы фасада](docs/facade-method-reference.md).
- [Настройка пакета в Joomla](docs/joomla-user-guide.md).
- [Thin wrapper architecture](docs/thin-wrapper-architecture.md).

Часть старых документов про низкоуровневые классы форка оставлена как исторический материал и требует отдельного обновления перед публикацией полного developer manual.

## Разработка и проверка

```powershell
php -l script.php
php D:/.agents/tools/php-qa/vendor/bin/phpunit --configuration=phpunit.xml
php build/release.php prepare-sdk
php build/release.php package-from-lock --package=lapaygroup/russianpost
```

## Лицензия

GNU General Public License v3.0. См. [`LICENSE`](LICENSE).
