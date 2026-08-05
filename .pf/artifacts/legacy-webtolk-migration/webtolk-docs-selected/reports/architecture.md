# Architecture

## Revision Trigger
- The original assignment included a Joomla-native facade-first library with selected internal donor entities.
- The current code drifted to a facade-only internal model.
- User clarified on 2026-04-22 that arrays are only the external return contract; the internal entity layer remains required.

## Current State
- Repository contains:
  - `.webtolk` development-flow pack and logs
  - old historical Webtolk Russian Post library under `docs/Webtolk-joomla-library/`
  - donor LapayGroup Russian Post library under `docs/lapay-group-russian-post-library/RussianPost-1.0.2/`
  - current implementation under `lib_webtolk_otpravkapochtaru/` and `plg_system_wt_otpravkapochtaru/`
  - build bridge via `phing.xml` and `.webtolk/build/package.config.json`
- Confirmed references show these patterns:
  - WT AmoCRM: one main library class, plugin-stored params, direct Joomla API usage
  - WT CDEK: facade `Cdek.php`, request helper `CdekRequest.php`, grouped `Entities/*`, thin traits, array-first public contract
  - LapayGroup: donor coverage, but generic SDK/provider structure around Guzzle and direct SOAP usage
- Current implementation drift:
  - `TrackingEntity.php` is absent
  - `Entity/*` is absent
  - facade methods currently accept raw arrays directly in places where the approved design expects selected entities to help payload correctness

## Target State
The target implementation remains a Joomla-native package composed of:
1. A `library` extension with namespace `Webtolk\\Otpravkapochtaru`
2. A companion system plugin for credentials and operational settings
3. One main facade class that exposes the public API for the internal plugin
4. A very small number of technical helper classes only where transport boundaries justify them
5. Donor-derived entities only for real domain objects, not for every API section

Proposed runtime structure:

```text
library package
|-- library manifest
|-- src/
|   |-- Otpravkapochtaru.php
|   |-- Request.php
|   |-- SoapRequest.php
|   |-- TrackingEntity.php
|   |-- Entity/
|   |   |-- Order.php
|   |   |-- Recipient.php
|   |   |-- ReturnShipment.php
|   |   |-- Item.php
|   |   |-- AddressReturn.php
|   |   |-- CustomsDeclaration.php
|   |   |-- CustomsDeclarationItem.php
|   |   `-- EcomData.php
|   |-- Enum/
|   |-- Exception/
|   |-- Traits/
|   `-- Fields/
`-- optional language assets

plugin package
|-- manifest
|-- services/provider.php
`-- src/Extension/...
```

## Public Contract Boundary
- `Otpravkapochtaru` is the only public entry point expected by the internal plugin and other internal callers.
- REST method groups from donor `OtpravkaApi` and `Calculation` are exposed through the facade, not through public service objects.
- `TrackingEntity` remains an internal collaborator behind the facade because SOAP is a true transport boundary.
- Public methods return plain arrays or arrays with normalized error payloads.
- Selected `Entity/*` classes are internal architecture components for request construction and payload shaping; they are not the public return contract.
- No backward-compatibility requirement exists with the old Webtolk Russian Post class.

## Design Decisions
1. Package as Joomla `library` extension plus companion plugin.
   - Reason: this is the confirmed Joomla-native deployment model from local docs and WT AmoCRM.
2. Keep one public facade class.
   - Reason: this matches established Webtolk/Joomla practice better than a public service graph and is sufficient because the consumer is our own plugin.
3. Use one REST request helper over Joomla HTTP instead of multiple public service classes.
   - Reason: Joomla core already provides `HttpFactory`; a single request layer keeps transport code out of the facade without pushing the design into framework-style services.
4. Keep `SoapRequest` as a dedicated SOAP transport helper and let `TrackingEntity` switch donor `single`/`pack` modes internally.
   - Reason: this keeps SOAP transport isolated without making external consumers care about it.
5. Port donor entities selectively and literally where they describe stable business objects.
   - Reason: `Order`, `Recipient`, `ReturnShipment`, `Item`, `CustomsDeclaration` and related classes are genuine domain objects; the array-first public contract does not cancel this internal entity layer.
6. Do not make `Value Object` a target architecture layer for responses.
   - Reason: for Joomla this adds ceremony without clear benefit; arrays remain the approved outward contract.
7. Use traits only for thin cache/log reuse and only if repetition really appears.
   - Reason: Joomla already has cache and log APIs.

## Interfaces And Dependencies
- Joomla core dependencies to prefer:
  - `Joomla\\CMS\\Http\\HttpFactory`
  - `Joomla\\CMS\\Plugin\\PluginHelper`
  - `Joomla\\Registry\\Registry`
  - `Joomla\\CMS\\Log\\Log`
  - `Joomla\\CMS\\Factory`
  - standard Joomla manifest namespace registration
- External/runtime dependencies still justified:
  - PHP `SoapClient` for tracking
  - `ext-json`
- Confirmed reference mapping:
  - WT CDEK confirms `Facade + Request + Entities + Traits` as a valid modern Webtolk pattern for Joomla 5+
  - donor tracking confirms that Russian Post tracking is best handled by one tracking handler that internally supports both `single` and `pack` SOAP modes

## Risk Controls
- Before adding any helper class, verify Joomla core does not already provide the capability.
- Avoid introducing or preserving a `Service` layer just to mimic other frameworks.
- Do not interpret the array-return public contract as permission to remove the approved internal `Entity/*` layer.
- Avoid creating extra tracking result entities unless they become unavoidable; the approved external contract is plain arrays and error arrays.
- Keep binary document handling simple; do not keep a dedicated value-object layer unless repeated logic proves it necessary.
- Keep tracking isolated from REST logic because SOAP has different runtime and error semantics.

## Rollout Order
1. Freeze package and plugin boundary
2. Restore the internal tracking boundary around `SoapRequest` plus donor-style `TrackingEntity`
3. Restore the selected donor `Entity/*` subset used for request construction and payload correctness
4. Refactor facade methods so the external contract stays array-first while internals use the approved entity-backed model
5. Port or realign donor methods onto the facade group by group against the restored architecture
6. Add only thin cache/log traits if repeated code appears
7. Re-run assurance and packaging checks against the revised structure
