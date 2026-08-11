# Worker Launch Prompt

You are a Process Forge shell-worker.
Requested model: `gpt-5.3-codex-spark`.
You are not the orchestrator.

## Assignment

- task_id: `t35d-runtime-entity-hydration-fix`
- run_id: `lapaygroup-thin-wrapper-implementation-20260811`
- mode: corrective writer after failed T35C
- workspace_access_file: `.pf/runtime/agent-runs/lapaygroup-thin-wrapper-implementation-20260811/t35d-runtime-entity-hydration-fix/workspace-access.json`

## One Job

Finish the runtime contract fix left incomplete by T35C: make `Otpravkapochtaru.php` construct real upstream LapayGroup entity objects without old fork classes or nonexistent `fromArray()` calls.

## Required Tooling

- Use PHPStorm MCP first for repository/file inspection where possible.
- Use named PHPStorm MCP tools if available: `read_file`, `lint_files`, `get_file_problems`.
- Use shell only for PHP syntax and focused text searches.

## Current Known State

- `Otpravkapochtaru.php` imports:
  - `LapayGroup\RussianPost\Entity\Order as LapayOrder`;
  - `LapayGroup\RussianPost\Entity\Recipient as LapayRecipient`;
  - `LapayGroup\RussianPost\Entity\ReturnShipment as LapayReturnShipment`.
- PHPStorm reports errors:
  - `LapayOrder::fromArray()` does not exist;
  - `LapayRecipient::fromArray()` does not exist;
  - `LapayReturnShipment::fromArray()` does not exist.
- Local upstream reference files:
  - `.pf/tmp/LapayGroup-RussianPost-2.0.0/RussianPost-2.0.0/src/Entity/Order.php`
  - `.pf/tmp/LapayGroup-RussianPost-2.0.0/RussianPost-2.0.0/src/Entity/Recipient.php`
  - `.pf/tmp/LapayGroup-RussianPost-2.0.0/RussianPost-2.0.0/src/Entity/ReturnShipment.php`
  - `.pf/tmp/LapayGroup-RussianPost-2.0.0/RussianPost-2.0.0/src/Entity/AddressReturn.php`
- `src/Transport` currently has no product purpose if it is empty or unreferenced.

## File Ownership

You may edit only:

- `lib_webtolk_otpravkapochtaru/src/Otpravkapochtaru.php`
- `lib_webtolk_otpravkapochtaru/src/Joomla/CredentialsProvider.php`
- `lib_webtolk_otpravkapochtaru/src/Joomla/Psr18TransportFactory.php`
- `lib_webtolk_otpravkapochtaru/src/Joomla/UploadedFileSerializer.php`
- `lib_webtolk_otpravkapochtaru/src/Transport/UploadedFileSerializer.php`
- `.pf/artifacts/worker-lapaygroup-thin-wrapper-runtime-entity-hydration-fix-20260811.md`

Do not edit Composer/build/CI/manifests/plugin/assets/docs/tests.

## Required Changes

- Replace every nonexistent `::fromArray()` usage with local hydration logic based on upstream setters.
- Prefer a small private helper in `Otpravkapochtaru.php`, for example:
  - convert payload keys such as `order-num`, `order_num`, `raw-full-name`, `postoffice-code` to setter names like `setOrderNum`, `setRawFullName`, `setPostofficeCode`;
  - call setters only when `method_exists($entity, $setter)`;
  - throw `InvalidArgumentException` for unknown keys only if the current codebase already follows that strict behavior; otherwise skip unknown keys to keep wrapper tolerant.
- Special-case return shipment nested addresses:
  - use `LapayGroup\RussianPost\Entity\AddressReturn`;
  - hydrate `address-from`/`address_from` and `address-to`/`address_to` arrays into `AddressReturn`.
- Preserve current public facade method names/signatures as much as possible.
- Do not add support for old fork entity classes.
- Keep only one uploaded-file serializer; if `src/Transport/UploadedFileSerializer.php` is unreferenced, delete it or leave the directory empty for T36 to remove.

## Verification

- PHPStorm MCP inspect edited PHP files.
- Run `php -l` for edited PHP files.
- Run focused search proving:
  - no `::fromArray(` remains in `lib_webtolk_otpravkapochtaru/src/Otpravkapochtaru.php`;
  - no old fork namespaces are referenced by kept runtime facade/Joomla helper files;
  - no `Transport\UploadedFileSerializer` reference remains.

## Output

Write `.pf/artifacts/worker-lapaygroup-thin-wrapper-runtime-entity-hydration-fix-20260811.md`.
Include verdict, files changed/deleted, commands run, PHPStorm inspection summary, and residual risks.
