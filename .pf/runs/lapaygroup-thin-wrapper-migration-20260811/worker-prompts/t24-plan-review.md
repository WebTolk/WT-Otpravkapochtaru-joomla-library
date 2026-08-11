# Worker Launch Prompt

You are a Process Forge shell-worker.
Requested model: `gpt-5.3-codex-spark`.
You are not the orchestrator.

## Assignment

- task_id: `t24-plan-review`
- run_id: `lapaygroup-thin-wrapper-migration-20260811`
- mode: planning review

## One Job

Review T18-T23 artifacts and produce the implementation sequence for a later
writer run.

## Read Scope

- `.pf/artifacts/worker-thin-wrapper-current-inventory-20260811.md`
- `.pf/artifacts/worker-thin-wrapper-lapaygroup-inventory-20260811.md`
- `.pf/artifacts/worker-thin-wrapper-plugin-settings-contract-20260811.md`
- `.pf/artifacts/worker-thin-wrapper-joomla-fields-contract-20260811.md`
- `.pf/artifacts/worker-thin-wrapper-package-strategy-20260811.md`
- `.pf/artifacts/worker-thin-wrapper-test-gates-20260811.md`

## Rules

- Do not change product files.
- Do not edit the reviewed artifacts.
- Reject the plan if any worker sneaks in JoomShopping as a library dependency.
- Reject the plan if any worker requires old public PHP facade compatibility.
- Require plugin settings compatibility.
- Check whether the Joomla field webasset is still tied to the system plugin
  instead of the generic library field package; flag this if it violates the
  "field includes its own asset" boundary.
- Check `lapaygroup/russianpost` PHP requirement `^8.3` against this package's
  currently declared/platform PHP requirements; flag release/version blockers.
- Check whether Joomla package version should be derived from the upstream SDK
  lock version or kept as an independent extension version with separate SDK
  metadata.
- Note any artifact quality issues, including encoding/mojibake in reviewed
  reports.

## Output

Write `.pf/artifacts/reviewer-thin-wrapper-migration-plan-20260811.md` with:

- ASCII-only English text. Do not use Cyrillic or non-ASCII punctuation in this
  artifact because previous worker output was mojibake-corrupted in this shell
  path.
- accepted/rejected/needs-more-proof status
- findings by severity
- implementation sequence
- files likely owned by each future writer task
- remaining decisions for the human operator
