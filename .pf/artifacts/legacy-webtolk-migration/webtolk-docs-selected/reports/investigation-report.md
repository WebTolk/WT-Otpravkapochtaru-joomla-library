# Investigation Report

## Question
How should the new `Webtolk\Otpravkapochtaru` Joomla library be rebuilt so it covers the LapayGroup Russian Post API method surface while following Joomla-first architecture and avoiding unnecessary custom abstractions?

## Context
- The current repository does not yet contain the new implementation.
- `docs/Webtolk-joomla-library/` contains an old Webtolk Russian Post library and is now a historical reference only.
- `docs/lapay-group-russian-post-library/RussianPost-1.0.2/` contains the donor library under MIT license and defines the required functional coverage.
- `D:/Dev/WT-Amo-CRM-library-for-Joomla-4/` is available as a Webtolk packaging and library-reference example.
- The path for the WT CDEK reference repository was not found in `D:/Dev` during this investigation.
- Project constraints require Joomla core-first decisions, PhpStorm MCP for file work, Serena for semantic analysis, local Joomla docs from `D:/.agents/docs/`, and Context7 Joomla manual documentation.

## Evidence
- Old Webtolk Russian Post library:
  - `docs/Webtolk-joomla-library/src/Otpravkapochtaru.php` is a single static class with a broad mixed responsibility surface.
  - `Serena` inspection shows methods like `getAccountInfo`, `getUserShippingPoints`, `cleanAddress`, `getPochtaRuShippingPrice`, `findPostofficeByAddress`, `createPochtaRuOrder`, `getApiLimit`.
  - `Otpravkapochtaru/getResponse` reads plugin params through `PluginHelper`, builds headers manually, and uses `Joomla\Http\HttpFactory`.
- LapayGroup donor library:
  - `composer.json` declares MIT license, `guzzlehttp/guzzle`, `psr/log`, `ext-soap`, `ext-json`.
  - `Providers/OtpravkaApi.php` contains the main REST surface for Otpravka, Delivery, Postoffice and Returns endpoints.
  - `Providers/Calculation.php` contains tariff and category calculation endpoints.
  - `Providers/Tracking.php` uses native `SoapClient` for tracking operations.
  - `TariffCalculation.php`, `CalculateInfo.php`, `ParcelInfo.php`, list classes and `Entity/*` provide data containers and helper objects.
- Joomla guidance:
  - Local article `D:/.agents/docs/joomla-development-articles/podklyuchenie-storonnih-php-bibliotek-v-joomla-web-tolk.md` confirms the Joomla library-extension wrapping model, namespace declaration in manifest, plugin-based settings storage, and library update caveats.
  - Local `joomla-architecture-rules.md` requires Joomla 5/6 conventions, DI, namespaced classes, `defined('_JEXEC') or die;`, and a Joomla-native approach.
  - Context7 `/joomla/manual` confirms service-provider and dependency-injection patterns for Joomla extensions and use of `Registry` for params handling.
- WT AmoCRM reference:
  - `lib_webtolk_amocrm/amocrm.xml` is a standard Joomla `library` manifest with `<libraryname>` and `<namespace path="src">`.
  - `lib_webtolk_amocrm/src/Amocrm.php` shows an older Webtolk pattern: one main class, plugin-stored settings, `HttpFactory`, cache, log, and helper form fields.

## Hypotheses
- The required public capability set should be derived from LapayGroup providers and core data objects, not from the old Webtolk API.
- Because backward compatibility is explicitly out of scope, the new library can expose a cleaner, Joomla-native API organized around actual functional domains.
- Joomla already covers the core needs for HTTP, configuration retrieval, manifest packaging, logging and namespaced library installation, so Guzzle-specific abstractions from LapayGroup should not be copied as-is.
- SOAP tracking likely remains the only area where a direct PHP transport dependency is still needed because Joomla core does not replace `SoapClient`.

## Findings
1. The donor library has a much broader functional surface than the old Webtolk wrapper.
   - The old Webtolk class exposes only a relatively small subset of operations around account info, shipping points, address and phone cleaning, tariff lookup, post office search, order creation and API limits.
   - LapayGroup covers settings, balances, recipient trust checks, order CRUD, backlog and batch flows, document generation, delivery tariffs, post office lookup, returns, SOAP tracking, tariff calculators and multiple entities/enums.
2. LapayGroup architecture is transport- and vendor-oriented, not Joomla-oriented.
   - `OtpravkaApi` centralizes several endpoint families behind one Guzzle-based client.
   - `Tracking` uses SOAP directly.
   - Entities and helper objects are generic PHP objects and collections.
3. The old Webtolk implementation proves some Joomla-native integration choices.
   - Plugin params are a viable source for credentials and library settings.
   - `Joomla\Http\HttpFactory` is already sufficient for REST calls to Russian Post.
4. The WT AmoCRM reference confirms the Joomla packaging direction.
   - A Joomla `library` extension with namespace registration is the correct packaging base.
   - A companion plugin remains the practical place for editable credentials and related settings.
5. The Joomla-first constraint materially changes the porting strategy.
   - We should port functional coverage, not class-for-class implementation.
   - We should reuse Joomla core for HTTP, configuration, caching/logging and packaging before introducing helpers.
6. A flat monolithic class would repeat the weaknesses of the old Webtolk and WT AmoCRM patterns.
   - Given that compatibility is not required, the library should be organized by Russian Post functional domains that already exist in the donor surface.
7. The WT CDEK architectural reference could not yet be inspected.
   - The repository path from the intake notes was not found locally, so no CDEK-specific structural evidence was available in this stage.

## Confirmed Root Cause
The project currently lacks a Joomla-native implementation because the available solutions solve different problems:
- the old Webtolk library is too narrow and structurally legacy-oriented;
- the LapayGroup library has the required method coverage but is built as a generic PHP SDK around Guzzle and direct SOAP usage rather than around Joomla library-extension conventions.

## Remaining Unknowns
- The exact local path of the WT CDEK reference repository.
- Which subset of LapayGroup entities should remain explicit public PHP objects in the new API, and which can be reduced to arrays or Joomla `Registry`-style payloads.
- Whether the future internal plugin needs one facade service or is happy consuming several focused services directly.
- Whether any Russian Post flows require file-download handling semantics identical to LapayGroup `UploadedFile`, or whether Joomla-native file response handling should be defined differently.

## Recommendation
Move to the `architecture` stage with these constraints:
1. Use LapayGroup functional coverage as the source of truth.
2. Package the result as a Joomla `library` extension with namespace `Webtolk\Otpravkapochtaru`.
3. Store editable credentials and operational settings in a companion plugin, not inside ad hoc library config files.
4. Replace Guzzle transport with Joomla HTTP facilities wherever the donor uses REST.
5. Keep SOAP tracking only where Joomla has no equivalent abstraction.
6. Organize the new library around minimal domain services that mirror the donor capability groups:
   - Otpravka REST service
   - Calculation service
   - Tracking service
   - data objects/enums only where they materially clarify the API
7. Do not preserve legacy API names from the old Webtolk package unless the future plugin implementation explicitly depends on them.
