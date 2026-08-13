# Справочник методов фасада

Текущий фасад `Webtolk\Otpravkapochtaru\Otpravkapochtaru` содержит только методы, связанные с Joomla-настройкой и доступом к провайдерам LapayGroup.

## Провайдеры и служебные объекты

- `credentialsProvider(): Webtolk\Otpravkapochtaru\Joomla\CredentialsProvider`
- `transport(): LapayGroup\RussianPost\Http\Psr18Transport`
- `otpravkaApi(): LapayGroup\RussianPost\Providers\OtpravkaApi`
- `calculation(): LapayGroup\RussianPost\Providers\Calculation`
- `trackingApi(): LapayGroup\RussianPost\Providers\Tracking`

## Вспомогательные методы для Joomla Form

- `getAccountInfo(): array`
- `getApiLimit(): array`

Остальные операции вызываются у провайдеров LapayGroup, которые возвращают методы `otpravkaApi()`, `calculation()` и `trackingApi()`.
