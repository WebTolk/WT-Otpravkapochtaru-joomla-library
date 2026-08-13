# План русской документации API

Дата: 2026-08-13

## Цель

Создать подробную русскую документацию для публичного фасада `Webtolk\Otpravkapochtaru\Otpravkapochtaru` на базе реальных JSON-снимков ответов API Почты России и трекинга. Документация должна объяснять, что Joomla-библиотека является тонкой оберткой над `lapaygroup/russianpost 2.0.0`, расширяет покрытие API Почты России по сравнению с прежней версией библиотеки 2.0, добавляет трекинг и может использоваться внутри сторонних Joomla-расширений.

## Исходные источники

- Фасад: `lib_webtolk_otpravkapochtaru/src/Otpravkapochtaru.php`.
- Composer-зависимость: `lapaygroup/russianpost 2.0.0`.
- Снимки ответов: `docs/api-snapshots/latest/*.json`.
- Скрипт сбора: `tools/capture-api-snapshots.php`.
- Исключения сборщика: `.dist/build/package.config.json` исключает `docs/`, `tools/` и `*.md`.

## Сбор снимков

Команда полного безопасного прогона:

```powershell
& 'D:\OSPanel\modules\PHP-8.4\php.exe' .\tools\capture-api-snapshots.php --joomla-root=D:/OSPanel/home/joomla.local/public --output-dir=D:/Dev/WT-Otpravkapochtaru-joomla-library/docs/api-snapshots/latest
```

Скрипт пишет обезличенные JSON-конверты:

- `schema_version`
- `captured_at`
- `method`
- `group`
- `side_effects`
- `input`
- `status`
- `duration_ms`
- `result`
- `error`

По умолчанию включен режим `safe-error-inputs`: методы с побочными эффектами получают безопасные несуществующие ID или тестовые payload-данные, чтобы зафиксировать реальные ответы API/SDK без создания реальных отправлений. Для осознанного live-прогона предусмотрен флаг `--include-live-mutating`, но он не используется для публичной документации.

## Группы документации

1. Аккаунт, настройки и заказы:
   - `getAccountInfo`
   - `getShippingPoints`
   - `getApiLimit`
   - `getSettings`
   - `createOrders`
   - `editOrder`
   - `findOrderById`
   - `findOrderByShopId`
   - `findOrderByRpo`
   - `getRecipientReliability`
   - `getRecipientsReliability`
   - `deleteOrders`
   - `returnOrdersToNew`
2. Партии, документы и возвратные отправления:
   - `createBatch`
   - `getAllBatches`
   - `getOrdersInBatch`
   - `generateDocumentPackage`
   - `generateDocumentF103`
   - `createReturnShipment`
   - `createReturnShipments`
   - `editReturnShipment`
   - `deleteReturnShipment`
3. Тарифы, справочники, ОПС и трекинг:
   - `getTariff`
   - `getTariffAndDeliveryPeriod`
   - `getCountryList`
   - `searchPostOfficeByIndex`
   - `searchPostOfficeByAddress`
   - `searchPostOfficeByCoordinates`
   - `getPostOfficeServices`
   - `getPostalCodesInLocality`
   - `getOperationsByRpo`
   - `getNpayInfo`
   - `getTickets`
   - `getOperationsByTicket`

## Правила для примеров

- Каждый пример должен быть готов к копированию в Joomla-расширение.
- Использовать полные `use`-импорты.
- Не публиковать токены, логины, пароли, e-mail, телефоны, ИНН, HID и реквизиты договора.
- Не публиковать локальные абсолютные пути.
- Использовать Joomla API (`Registry`, `ArrayHelper`, `StringHelper`, `Factory`) только там, где это улучшает пример.
- Для методов с побочными эффектами явно указывать, что на валидных данных они создают, изменяют или удаляют сущности в API Почты России.

## Делегирование

Запрошенная пользователем модель `gpt-5.3 codex spark` не доступна в текущем списке MCP multi-agent. Для текстовых разделов используются воркеры `gpt-5.5`, reasoning `medium`; примитивные проверки выполняет основной агент.

- `t07b-docs-account-orders`: черновик получен от воркера `019ffa8c-b491-7c93-bebe-cffe423dc46f`.
- `t07c-docs-batches-returns`: черновик получен от воркера `019ffa8c-d06a-7f20-881e-4ad9114e5c3b`.
- `t07d-docs-tariffs-postoffices-tracking`: воркер `019ffa8f-e728-7090-bc9a-35a37945b081` запущен.

## Приемка

- `tools/capture-api-snapshots.php` проходит `php -l`.
- `docs/api-snapshots/latest/index.json` содержит 34 публичных метода.
- В `docs/api-snapshots/latest/*.json` нет известных секретов и персональных маркеров тестового аккаунта.
- `docs/` и `tools/` исключены из сборки пакета.
- README содержит системные требования, быстрый старт и один самодостаточный пример работы с библиотекой.
