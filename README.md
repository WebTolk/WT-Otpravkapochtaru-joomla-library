# WT Otpravkapochtaru: библиотека для Joomla

![Joomla 5+](https://img.shields.io/badge/Joomla-5%2B-1f6f8b)
![PHP 8.3+](https://img.shields.io/badge/PHP-8.3%2B-777bb4)
![API Почты России](https://img.shields.io/badge/API-%D0%9F%D0%BE%D1%87%D1%82%D0%B0%20%D0%A0%D0%BE%D1%81%D1%81%D0%B8%D0%B8-c41230)
![lapaygroup/russianpost 2.0](https://img.shields.io/badge/SDK-lapaygroup%2Frussianpost%202.0-2f855a)

Joomla-пакет `WT Otpravkapochtaru` подключает библиотеку `lapaygroup/russianpost` к Joomla, хранит параметры авторизации в системном плагине и предоставляет готовые Joomla Form поля для настроек расширений. Основная работа с API Почты России выполняется через методы LapayGroup SDK, а класс `Webtolk\Otpravkapochtaru\Otpravkapochtaru` остается тонким фасадом, который только подготавливает авторизацию, транспорт и несколько вспомогательных методов для полей формы.

## Системные требования

- Joomla 5 или новее.
- PHP 8.3 или новее.
- PHP extension `mbstring`.
- PHP extension `soap` нужен только для SOAP-трекинга; установка пакета не блокируется без него, но методы трекинга работать не будут.
- Для сборки из исходников нужны Composer и PHP extension `zip`.

## Быстрый старт

1. Установите ZIP-пакет через админку Joomla: `System -> Install -> Extensions`.
2. Откройте `System -> Plugins`, найдите `System - WT Otpravkapochtaru` и включите его.
3. Заполните `AccessToken`.
4. Выберите режим пользовательской авторизации и укажите `user_auth_key` либо пару `user_login` / `user_password`.
5. Для SOAP-трекинга заполните `tracking_login` и `tracking_password`.
6. Используйте фасад в своем Joomla-расширении: он сам возьмет параметры включенного системного плагина и вернет настроенные клиенты LapayGroup.

## Пример кода

Пример получает список доступных ОПС через метод LapayGroup SDK, при этом авторизация и транспорт берутся из параметров Joomla-плагина.

```php
<?php

declare(strict_types=1);

use Joomla\Utilities\ArrayHelper;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$client = new Otpravkapochtaru();
$shippingPoints = $client->otpravkaApi()->shippingPoints();

foreach ($shippingPoints as $point) {
    $postcode = (string) ($point['operator-postcode'] ?? '');
    $address = (string) ($point['ops-address'] ?? $point['address'] ?? '');
    $mailTypes = ArrayHelper::getColumn($point['user-available-products'] ?? [], 'mail-type');

    echo trim($postcode . ' ' . $address) . PHP_EOL;
    echo implode(', ', array_unique(array_filter($mailTypes))) . PHP_EOL;
}
```

## Документация

- [Индекс документации](docs/README.md)
- [Публичный контракт фасада](docs/public-api.md)
- [Архитектура тонкого фасада](docs/Joomla-wrapper-architecture.md)
- [Настройка пакета в Joomla](docs/joomla-user-guide.md)
- [Joomla Form поля](docs/joomla-form-fields.md)
- [JSON-снимки ответов API](docs/api-snapshots/README.md)

## Устанавливаемые расширения

- `WT Otpravkapochtaru library` - PHP-библиотека с namespace `Webtolk\Otpravkapochtaru`.
- `System - WT Otpravkapochtaru` - системный плагин Joomla для хранения параметров API и демонстрации Joomla Form полей.

## Лицензия

GNU General Public License v3.0. См. [LICENSE](LICENSE).
