# Низкоуровневый слой

Низкоуровневый слой нужен только для сборки SDK-клиентов внутри Joomla. Обычно расширениям достаточно создать `Otpravkapochtaru` и получить один из провайдеров LapayGroup.

## Авторизация

`Webtolk\Otpravkapochtaru\Joomla\CredentialsProvider` читает параметры системного плагина `wtotpravkapochtaru` либо принимает явный массив, `Registry` или legacy-объект с методом `params()`. Он возвращает `AccessToken`, значение `X-User-Authorization`, логин и пароль SOAP-трекинга, а также таймаут HTTP-запросов.

## Транспорт

`Webtolk\Otpravkapochtaru\Joomla\Psr18TransportFactory` создает `LapayGroup\RussianPost\Http\Psr18Transport` на базе Joomla HTTP клиента и Laminas PSR-7 фабрик. Этот транспорт передается в `OtpravkaApi` и `Calculation`; трекинг использует SOAP-клиент LapayGroup.

## Прямой Доступ

```php
<?php

declare(strict_types=1);

use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

defined('_JEXEC') or die;

$client = new Otpravkapochtaru();
$transport = $client->transport();
$credentials = $client->credentialsProvider();
```
