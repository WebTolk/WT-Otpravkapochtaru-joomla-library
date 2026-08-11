# Worker Audit: Composer/GitHub SOAP Requirement

run_id: soap-policy-worker-audit-20260811
task_id: t30-composer-soap-requirement
mode: read-only product audit
requested_model: gpt-5.3-codex-spark
assignment_capsule: `.pf/runtime/agent-runs/soap-policy-worker-audit-20260811/t30-composer-soap-requirement/workspace-access.json`

## Verdict
pass

## Checks and Evidence
- composer.json requires PHP minimum 8.3.0 and includes SOAP-related extensions explicitly.
  - [composer.json](composer.json): `require` contains `php: ">=8.3.0"`, `ext-mbstring`, `ext-simplexml`, and `ext-soap`.
- composer.json sets the Composer platform PHP version to 8.3.0.
  - [composer.json](composer.json): `config.platform.php` is `8.3.0`.
- README distinguishes build-side Composer/GitHub dependency from ready ZIP install behavior.
  - [README.md](README.md): text documents that `ext-soap` is declared in `composer.json` for Composer/GitHub build behavior and that Joomla package installation can proceed without SOAP, showing a warning path instead of install blocking.
- No evidence indicates Joomla installation is blocked when SOAP is missing.
  - [README.md](README.md): states ZIP installation is not hard blocked on SOAP absence and mentions optional tracking behavior.
  - [README.md](README.md): includes warning wording for tracking methods when SOAP is unavailable.
- Workspace `.github` scope check confirms no extra workflow constraints are present in repository.
  - [`.github` missing](.github): folder is not present in repository at this moment.
- Supporting audit trail confirms correction applied and aligned with this policy.
  - [.pf/artifacts/joomla-system-requirements-php83-mbstring-20260811.md](.pf/artifacts/joomla-system-requirements-php83-mbstring-20260811.md)
  - [.pf/artifacts/orchestrator-release-readiness-worker-review-20260811.md](.pf/artifacts/orchestrator-release-readiness-worker-review-20260811.md)

## Residual Risk
- Medium confidence that future edits could reintroduce divergence if `README.md` or installer messaging is later changed without matching `composer.json`.
- Current check is documentation-and-metadata based; no runtime test execution was performed in this shell-worker pass beyond reading allowed artifacts.
