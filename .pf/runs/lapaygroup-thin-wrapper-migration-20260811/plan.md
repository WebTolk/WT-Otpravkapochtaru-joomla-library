# Run Plan: lapaygroup-thin-wrapper-migration-20260811

## Goal

Plan a migration from the current forked Russian Post API implementation to a
thin Joomla library wrapper around `lapaygroup/russianpost` 2.0.0.

The Joomla library must keep only Joomla-specific value:

- system plugin settings and settings migration compatibility
- Joomla Form fields for convenient work with Russian Post Otpravka entities
- WebAsset registration for those fields
- Joomla-way HTTP/PSR dependency wiring
- packaging/install/runtime integration
- GitHub Actions release build that refreshes `lapaygroup/russianpost` through
  Composer, following the WT Max library build pattern

The library must not contain JoomShopping integration. JoomShopping or any other
consumer may use the generic Joomla fields, but the library stays
consumer-neutral.

No backward compatibility is required for the old 3.0.0 public PHP facade/API.
Backward compatibility is required only for existing system plugin parameter
storage.

## Worker Policy

- All workers use `gpt-5.3-codex-spark`.
- Workers are intentionally narrow and dumb.
- Default mode is read-only planning.
- No product code changes in this planning run.
- No stand changes in this planning run.
- No raw secrets in artifacts.

## Task Order

1. T18: inventory current library code and classify keep/remove/replace.
2. T19: inventory LapayGroup 2.0.0 SDK surface available from local source.
3. T20: define system plugin settings compatibility contract.
4. T21: inventory generic Joomla Form fields and their data needs.
5. T22: define thin wrapper composition and WT Max-style Composer/GitHub package
   strategy.
6. T23: define tests and release gates.
7. T24: review all planning artifacts and produce implementation sequence.
