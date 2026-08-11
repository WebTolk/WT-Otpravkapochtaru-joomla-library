# Orchestrator Review: Thin Wrapper Worker Run

Run: `lapaygroup-thin-wrapper-migration-20260811`
Reviewer: main orchestrator
Product code changed: no

## Worker Execution

Launched Process Forge shell-workers:

- T18 `t18-current-library-inventory`
- T19 `t19-lapaygroup-sdk-inventory`
- T20 `t20-plugin-settings-contract`
- T21 `t21-joomla-fields-contract`
- T22 `t22-wrapper-package-strategy`
- T23 `t23-test-gates`
- T24 `t24-plan-review`

All requested worker artifacts exist.

Operational note: the local PowerShell launch path leaves some heartbeat files
at `starting` because Codex CLI writes a banner to stderr and PowerShell treats
it as a native command error under `$ErrorActionPreference = 'Stop'`. The worker
artifacts and `codex-output.txt` files were still produced. Future launch
scripts should avoid `Stop` for native stderr or wrap the Python worker call.

## Accepted Findings

- T18 correctly separates Joomla-specific value from old fork code:
  - keep/rework Joomla fields, field option service and credentials adapter;
  - replace legacy REST/SOAP transport and duplicated entity layer with SDK
    paths where parity exists.
- T19 confirms local SDK coverage for:
  - `OtpravkaApi`
  - `Calculation`
  - `Tracking`
  - PSR-18/PSR-17 transport construction.
- T20 keeps the compatibility boundary in the right place:
  - only system plugin params are protected;
  - old unreleased public PHP API compatibility is not required.
- T22 captures the correct WT Max-style release direction:
  - Composer refresh on GitHub release;
  - lockfile-driven SDK metadata;
  - package-local runtime copy and package-local autoload;
  - no Joomla core vendor edits.
- T23 defines the right release gates:
  - plugin params upgrade;
  - classloader/package inspection;
  - generic Joomla Form rendering;
  - read-only API smoke;
  - no JoomShopping coupling.

## Required Corrections Before Implementation

1. Field asset ownership is not settled.
   - T21/T23 still describe `plg_system_wtotpravkapochtaru.linked-select-fields`
     as the field webasset.
   - For a generic library field package, the asset should be library-owned or
     the plugin-bound ownership must be explicitly justified.

2. PHP baseline is a hard blocker.
   - `lapaygroup/russianpost` requires PHP `^8.3` plus `ext-soap` and
     `ext-mbstring`.
   - Current extension manifests/installer guards must be checked and updated
     before release planning proceeds.

3. Version policy must be explicit.
   - The Joomla extension package version should likely remain independent
     (`3.0.0` for this migration).
   - SDK version/date from `composer.lock` should be stored as SDK metadata,
     not blindly used as the extension package version unless the operator
     explicitly chooses SDK-coupled versioning.

4. Tracking scope remains undecided.
   - LapayGroup tracking uses SOAP.
   - Decide whether tracking is part of this thin-wrapper release or a separate
     adapter/follow-up.

5. Artifact quality issue.
   - T20 contains minor mojibake in its heading.
   - First T24 run produced severe mojibake and was rerun with ASCII-only output.
   - The final T24 artifact is ASCII-only and readable.

## Review Verdict

Status: `needs-more-proof`.

The worker set is good enough to feed a later implementation plan, but not good
enough to start broad product-code rewrites until the four decisions above are
closed.

No worker introduced JoomShopping as a library dependency.
No worker required old unreleased public facade compatibility.
Plugin settings compatibility remains a required invariant.
