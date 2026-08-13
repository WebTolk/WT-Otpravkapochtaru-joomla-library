# WT Otpravkapochtaru Joomla Library

![Joomla 5+](https://img.shields.io/badge/Joomla-5%2B-1f6f8b)
![PHP 8.3+](https://img.shields.io/badge/PHP-8.3%2B-777bb4)
![API Почты России](https://img.shields.io/badge/API-%D0%9F%D0%BE%D1%87%D1%82%D0%B0%20%D0%A0%D0%BE%D1%81%D1%81%D0%B8%D0%B8-c41230)
![Обертка над lapaygroup/russianpost 2.0](https://img.shields.io/badge/%D0%BE%D0%B1%D0%B5%D1%80%D1%82%D0%BA%D0%B0-lapaygroup%2Frussianpost%202.0-2f855a)

Joomla-пакет предназначен для работы с API «Отправка» Почты России и SOAP-трекингом. Версия 3.x стала тонкой Joomla-оберткой над `lapaygroup/russianpost 2.0.0`: библиотека использует исходную SDK-библиотеку, хранит настройки в системном плагине Joomla и предоставляет единый фасад `Webtolk\Otpravkapochtaru\Otpravkapochtaru` для ваших расширений.

По сравнению с прежней веткой библиотеки 2.0 расширено покрытие API Почты России: добавлены методы аккаунта, настроек, партий, документов, возвратных отправлений, справочников, ОПС, тарифов и трекинга.

## Системные требования

- Joomla 5 или новее.
- PHP 8.3 или новее.
- PHP extension `mbstring`.
- PHP extension `soap` нужен для SOAP-трекинга; установка пакета не блокируется, но трекинг без него работать не будет.
- Для сборки из исходников нужны Composer, `ext-soap` и `ext-zip`.

## Быстрый старт

1. Установите ZIP-пакет через админку Joomla: `System -> Install -> Extensions`.
2. Откройте `System -> Plugins`.
3. Найдите и включите `System - WT Otpravkapochtaru`.
4. Заполните `AccessToken`.
5. Выберите режим авторизации пользователя и укажите `user_auth_key` либо пару `user_login` / `user_password`.
6. Для трекинга заполните `tracking_login` и `tracking_password`.
7. Используйте фасад библиотеки в своем Joomla-расширении.

## Пример кода

Пример получает список доступных ОПС из настроек аккаунта. Код рассчитан на выполнение внутри Joomla после установки пакета и настройки системного плагина.

```php
<?php

declare(strict_types=1);

use Joomla\Utilities\ArrayHelper;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$client = new Otpravkapochtaru();
$shippingPoints = $client->getShippingPoints();

foreach ($shippingPoints as $point) {
    $postcode = (string) ($point['operator-postcode'] ?? '');
    $address = (string) ($point['ops-address'] ?? '');
    $enabledServices = ArrayHelper::getColumn($point['services'] ?? [], 'code');

    echo trim($postcode . ' ' . $address) . PHP_EOL;
    echo implode(', ', $enabledServices) . PHP_EOL;
}
```

## Документация

- [Индекс документации](docs/README.md)
- [Публичный API фасада](docs/public-api.md)
- [Реальные JSON-снимки ответов API](docs/api-snapshots/README.md)
- [Архитектура тонкой обертки](docs/thin-wrapper-architecture.md)
- [Настройка пакета в Joomla](docs/joomla-user-guide.md)

## Устанавливаемые расширения

- `WT Otpravkapochtaru library` - PHP-библиотека с namespace `Webtolk\Otpravkapochtaru`.
- `System - WT Otpravkapochtaru` - системный плагин Joomla для хранения настроек API и примеров использования.

## Лицензия

GNU General Public License v3.0. См. [LICENSE](LICENSE).
