# Run Plan: LapayGroup RussianPost Joomla Local Validation

Run id: `lapaygroup-russianpost-joomla-local-validation-20260811`

Status: `planned`

Objective:

Validate `lapaygroup/russianpost` 2.0.0 on `joomla.local` using Joomla-way HTTP
infrastructure before any product-code migration.

Constraints:

- Product code is read-only.
- Use Process Forge shell-workers with `gpt-5.3-codex-spark`.
- Experiments run only in test-stand scratch files.
- No secrets in artifacts.

Tasks:

1. `t07-lapaygroup-stand-dependency-probe`
2. `t08-lapaygroup-joomla-psr-transport-prototype`
3. `t09-lapaygroup-runtime-smoke`
4. `t10-lapaygroup-data-parity-risk-matrix`
5. `t11-lapaygroup-test-plan-review`

Primary artifact:

- `.pf/artifacts/lapaygroup-russianpost-joomla-local-test-plan-20260811.md`

Reviewer:

- model: `gpt-5.5`
- reasoning effort: `high`
- reason: the review is architecture and release-risk oriented, with PHP
  baseline, Joomla-way transport, live API safety, credential handling and
  data-parity gates.
