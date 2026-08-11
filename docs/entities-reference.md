# Entity Surface Status

Version 3.0.0 removed the package-owned fork entities from the public API.

The facade still accepts associative arrays for orders, recipients, returns, customs data, and related payloads. Internally it hydrates upstream `LapayGroup\RussianPost` entities before calling the SDK providers.

## Current Guidance

- Application code should call `Webtolk\Otpravkapochtaru\Otpravkapochtaru`.
- New payloads should use API-style keys, for example `index-to`, `recipient-name`, `mail-type`, `mail-category`, and `mass`.
- Existing payloads using common camelCase keys are normalized by the facade where compatibility is implemented.
- Direct entity usage should target upstream SDK classes under `LapayGroup\RussianPost\Entity`.

The old package-owned entity examples are available only in Git history and should not be used for new integrations.
