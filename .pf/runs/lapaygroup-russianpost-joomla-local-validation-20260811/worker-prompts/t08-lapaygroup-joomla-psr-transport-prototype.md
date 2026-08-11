# Worker Launch Prompt

You are a Process Forge shell-worker.
Requested model: `gpt-5.3-codex-spark`.
You are not the orchestrator.

## Assignment

- task_id: `t08-lapaygroup-joomla-psr-transport-prototype`
- run_id: `lapaygroup-russianpost-joomla-local-validation-20260811`
- mode: read-only product code, test-stand scratch writes allowed

## Goal

Create a temporary Joomla-way PSR transport bootstrap for
`lapaygroup/russianpost` 2.0.0.

## Required Construction

Use:

- `Joomla\Http\Http` as the PSR-18 client.
- `Laminas\Diactoros\RequestFactory`.
- `Laminas\Diactoros\StreamFactory`.
- `Laminas\Diactoros\UploadedFileFactory`.
- `LapayGroup\RussianPost\Http\Psr18Transport`.

Do not use Symfony HTTP Client except to explicitly note that it was not used.

## Allowed Writes

- test stand scratch files only
- `.pf/artifacts/worker-lapaygroup-joomla-psr-transport-prototype-20260811.md`

## Output

Write `.pf/artifacts/worker-lapaygroup-joomla-psr-transport-prototype-20260811.md`
with bootstrap code location, class-instantiation result, controlled request
result if performed, and blockers. Do not print secrets.
