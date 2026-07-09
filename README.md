# WT Otpravkapochtaru Joomla Library

Пакет Joomla для интеграции с API Почты России.

Пакет устанавливает:

- `WT Otpravkapochtaru library` - PHP-библиотеку с namespace `Webtolk\Otpravkapochtaru`;
- `System - WT Otpravkapochtaru` - системный плагин Joomla, где хранятся учетные данные и отображается статус подключения к API.

## Возможности

- Проверка аккаунта Почты России и лимитов API.
- Создание, редактирование, поиск и удаление черновиков отправлений.
- Проверка надежности получателя.
- Создание партий отправлений.
- Загрузка печатных документов партии.
- Расчет тарифов.
- Поиск отделений по индексу, адресу и координатам.
- SOAP-трекинг отправлений Почты России.
- Настройка учетных данных через стандартную форму плагина Joomla.

## Требования

- Joomla 5 или новее.
- PHP 8.1 или новее.
- PHP-расширение SOAP для методов трекинга.
- Учетные данные Почты России:
  - REST `access_token`;
  - пользовательский ключ или логин/пароль;
  - `tracking_login` и `tracking_password` для SOAP-трекинга.

## Быстрый старт

1. Скачайте или соберите ZIP-пакет расширения.
2. В админке Joomla откройте `Система` -> `Установка` -> `Расширения`.
3. Загрузите `WT Otpravkapochtaru_3.0.0.zip`.
4. Откройте `Система` -> `Плагины`.
5. Найдите `System - WT Otpravkapochtaru`.
6. Заполните настройки:
   - `access_token`;
   - режим авторизации;
   - `user_key` или `user_login` / `user_password`;
   - `tracking_login` и `tracking_password`, если нужен трекинг.
7. Включите плагин и сохраните его.
8. Проверьте блок статуса аккаунта в настройках плагина.

## Быстрый пример для разработчика

```php
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

$client = new Otpravkapochtaru();

$account = $client->getAccountInfo();
$countries = $client->getCountryList();
```

Создание черновика отправления:

```php
use Webtolk\Otpravkapochtaru\Entity\Order;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

$client = new Otpravkapochtaru();

$order = Order::fromArray([
    'order-num' => 'site-100500',
    'recipient-name' => 'Иванов Иван',
    'tel-address' => '+79990000000',
    'index-to' => '410012',
    'region-to' => 'Саратовская область',
    'place-to' => 'Саратов',
    'street-to' => 'Московская',
    'house-to' => '1',
    'mail-type' => 'POSTAL_PARCEL',
    'mail-category' => 'ORDINARY',
    'mass' => 1000,
]);

$result = $client->createOrders([$order]);
```

Расчет тарифа:

```php
$tariff = $client->getTariff(27030, [
    'from-index' => '410012',
    'to-index' => '455001',
    'mail-type' => 'POSTAL_PARCEL',
    'mail-category' => 'ORDINARY',
    'mass' => 1000,
]);
```

## Документация

Подробная документация находится в [`docs/`](docs/):

- [`docs/developer-api.md`](docs/developer-api.md) - документация разработчика.
- [`docs/facade-method-reference.md`](docs/facade-method-reference.md) - практические примеры для всех публичных методов фасада.
- [`docs/joomla-user-guide.md`](docs/joomla-user-guide.md) - пользовательская документация Joomla.

## Разработка и проверки

В рабочем окружении WebTolk проект использует общие QA-инструменты из `D:/.agents/tools/php-qa`.

Основные команды:

```powershell
php -l script.php
powershell -NoProfile -ExecutionPolicy Bypass -File tools/qa/lint-php.ps1
php D:/.agents/tools/php-qa/vendor/bin/phpunit --configuration=phpunit.xml
php D:/.agents/tools/php-qa/vendor/bin/phpstan analyse --configuration=phpstan.neon
php D:/.agents/tools/php-qa/vendor/bin/php-cs-fixer fix --dry-run --diff --config=.php-cs-fixer.dist.php
php D:/.agents/tools/php-qa/vendor/bin/phpcs --standard=phpcs.xml
```

## Лицензия

GNU General Public License v3.0. См. [`LICENSE`](LICENSE).
