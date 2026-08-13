# Аккаунт и конфигурация

Фасад читает параметры системного плагина Joomla и передает их в `LapayGroup\RussianPost\Providers\OtpravkaApi`. Состояние аккаунта и список ОПС можно получить напрямую через SDK, а методы `getAccountInfo()` и `getApiLimit()` оставлены в фасаде только как вспомогательные методы для информационного поля аккаунта.

## Настройки Аккаунта

```php
<?php

declare(strict_types=1);

use Joomla\Registry\Registry;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$client = new Otpravkapochtaru();
$settings = new Registry($client->otpravkaApi()->settings());

echo (string) $settings->get('account-name', '');
```

## ОПС Отправителя

```php
<?php

declare(strict_types=1);

use Joomla\Utilities\ArrayHelper;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$client = new Otpravkapochtaru();
$points = $client->otpravkaApi()->shippingPoints();

$postcodes = ArrayHelper::getColumn($points, 'operator-postcode');
print_r(array_values(array_filter($postcodes)));
```

## Вспомогательные Методы Для Полей

- `getAccountInfo()` вызывает `otpravkaApi()->settings()`.
- Поля выбора ОПС, видов и категорий отправлений используют `otpravkaApi()->shippingPoints()` напрямую.
- `getApiLimit()` выполняет служебный запрос `/1.0/settings/limit`, потому что отдельного публичного метода в поставляемой версии SDK нет.
