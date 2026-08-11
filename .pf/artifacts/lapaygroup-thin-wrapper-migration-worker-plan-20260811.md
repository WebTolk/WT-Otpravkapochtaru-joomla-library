# LapayGroup Thin Wrapper Migration Worker Plan

## Decision Boundary

The target is not "JoomShopping integration in the library".

The target is:

- thin Joomla wrapper around `lapaygroup/russianpost` 2.0.0
- generic Joomla Form fields for Russian Post Otpravka entities
- system plugin settings compatibility
- Joomla packaging/runtime integration
- GitHub Actions release build that refreshes the upstream SDK through Composer,
  using `WT-Max-Joomla-library` as the reference pattern

No backward compatibility is required for the old 3.0.0 public PHP facade/API.
Backward compatibility is required only for existing system plugin parameters.

## Worker Set

All workers use `gpt-5.3-codex-spark`.

### T18 Current Library Inventory

Read current library files and produce a keep/remove/replace table.

Expected coarse outcome:

- keep: Joomla fields, field assets, settings provider/migration shape
- replace: forked HTTP transport, forked API facade, duplicated entities/enums
- inspect: country dictionary, tracking, binary wrappers

### T19 LapayGroup SDK Inventory

Read local SDK source and identify exact provider/entity/enum classes that can
replace current forked code.

### T20 Plugin Settings Contract

Document the only compatibility promise:

- existing system plugin row remains
- existing parameter keys continue to be read
- new LapayGroup config is derived from existing params
- secrets are not renamed destructively during update

### T21 Generic Joomla Fields Contract

Document what fields exist, what entity data they need, how they handle API
errors, and what must remain consumer-neutral.

### T22 Wrapper/Package Strategy

Define how LapayGroup SDK is included/autoloaded in the Joomla package and what
minimal wrapper services are needed. This task must explicitly adapt the WT Max
Composer/GitHub release pattern:

- Composer dependency on `lapaygroup/russianpost`
- build-time `composer update`
- package metadata from `composer.lock`
- runtime SDK copy into the Joomla library tree
- package-local autoload for `LapayGroup\RussianPost\`
- GitHub release ZIP publishing

### T23 Tests And Gates

Define tests required before implementation and release:

- plugin settings preservation on update
- classloader proof
- field rendering proof
- read-only API smoke
- package inspection
- no JoomShopping coupling in the library

### T24 Planning Review

Review T18-T23 and produce the implementation task sequence.

## Non-Goals

- no public facade compatibility for old 3.0.0 API
- no JoomShopping-specific code in the library
- no product code changes in this planning run
- no live account mutation tests
