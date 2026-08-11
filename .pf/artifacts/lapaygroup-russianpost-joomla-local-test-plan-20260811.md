# LapayGroup RussianPost 2.0.0 Joomla Local Test Plan

Timestamp: 2026-08-11

## Scope

Validate whether `lapaygroup/russianpost` 2.0.0 can replace the current forked
REST core while keeping this Joomla package as a thin wrapper with Joomla
configuration, Joomla Form fields, WebAsset registration and integration code.

Product code must not be changed during this test plan. All experiments must
run on the Joomla local test stand in scratch files.

## Worker Model

Use Process Forge shell-workers with:

- `PF_AGENT_MODEL=gpt-5.3-codex-spark`
- `PF_CODEX_REASONING_EFFORT=medium`
- sandbox scoped to the project and Joomla local test stand scratch paths

The regular multi-agent tool available in this Codex session does not expose a
`gpt-5.3-codex-spark` model override, so this plan is written for Process Forge
shell-worker execution through the installed `codex_exec_worker.py` path.

## Test Stand Boundaries

Allowed runtime mutation:

- Joomla local test stand scratch directory, for example a temporary directory
  under `tmp/`.
- Temporary Composer project or vendor download used only for the experiment.
- Temporary verifier PHP files callable through browser/CLI.
- `.pf` run reports and worker reports.

Forbidden during the experiment:

- Product files under `lib_webtolk_otpravkapochtaru/`.
- Product files under `plg_system_wt_otpravkapochtaru/`.
- Product manifests and installer script.
- Current release package metadata.
- Git commits.

## Target Architecture To Prove

The proof-of-concept must use:

- `lapaygroup/russianpost` 2.0.0 as the upstream SDK.
- `Joomla\Http\Http` as the PSR-18 `ClientInterface`.
- Laminas Diactoros factories from Joomla vendor as PSR-17 factories:
  - `Laminas\Diactoros\RequestFactory`
  - `Laminas\Diactoros\StreamFactory`
  - `Laminas\Diactoros\UploadedFileFactory`
- `LapayGroup\RussianPost\Http\Psr18Transport`.

Symfony HTTP Client exists in Joomla vendor, but is not the target path for this
test. It may be mentioned only as a non-target fallback.

## Worker Breakdown

### Worker T07 - Stand Dependency Probe

Goal:

- Verify the Joomla local stand has the PSR interfaces and Laminas factories
  required to instantiate LapayGroup transport without adding Symfony runtime.

Expected checks:

- PHP version on the stand is `>= 8.3`.
- `Psr\Http\Client\ClientInterface` exists.
- `Psr\Http\Message\RequestFactoryInterface` exists.
- `Psr\Http\Message\StreamFactoryInterface` exists.
- `Psr\Http\Message\UploadedFileFactoryInterface` exists.
- `Joomla\Http\Http` implements `ClientInterface`.
- `Laminas\Diactoros\RequestFactory`, `StreamFactory`,
  `UploadedFileFactory` exist.
- Composer can install or download `lapaygroup/russianpost:2.0.0` into an
  isolated scratch directory.

Output:

- `.pf/artifacts/worker-lapaygroup-stand-dependency-probe-20260811.md`

### Worker T08 - Joomla PSR Transport Prototype

Goal:

- Build a temporary Joomla-way PSR transport bootstrap in the stand scratch
  area.

Prototype responsibilities:

- Load Joomla vendor autoload.
- Load the isolated LapayGroup vendor autoload.
- Instantiate:
  - `Joomla\Http\Http`
  - `Laminas\Diactoros\RequestFactory`
  - `Laminas\Diactoros\StreamFactory`
  - `Laminas\Diactoros\UploadedFileFactory`
  - `LapayGroup\RussianPost\Http\Psr18Transport`
- Prove the transport can build and send a harmless controlled request.

Preferred harmless request:

- First use a local synthetic endpoint if available.
- If no local endpoint is practical, use a read-only API call later in T09
  using real plugin credentials.

Output:

- `.pf/artifacts/worker-lapaygroup-joomla-psr-transport-prototype-20260811.md`

### Worker T09 - Runtime Smoke Against Russian Post API

Goal:

- Prove the upstream SDK works with real Joomla plugin credentials on
  `joomla.local`.

Read credentials from the installed Joomla system plugin parameters without
copying secrets into public artifacts.

Required smoke calls:

- `OtpravkaApi::settings()`
- `OtpravkaApi::shippingPoints()`
- one postoffice lookup used by current dependent fields, for example
  by postal code or service list
- one tariff calculation equivalent to the current known route

Rules:

- Do not create real orders.
- Do not mutate Russian Post account state.
- Do not print tokens, user keys, passwords or raw credential values.
- Report only booleans, response shape, selected non-secret keys and counts.

Output:

- `.pf/artifacts/worker-lapaygroup-runtime-smoke-20260811.md`

### Worker T10 - Data Parity And Loss-Risk Matrix

Goal:

- Compare current fork behavior against LapayGroup 2.0.0 behavior for the API
  surfaces this Joomla package actually uses.

Compare:

- settings/account info
- user shipping points
- mail type and mail category source data required by linked fields
- tariff calculation inputs and response shape
- order payload normalization and create/edit/delete method signatures
- batch document return type
- postoffice endpoint support
- tracking support and credentials model

Data loss checks:

- Which current entities/arrays cannot be represented by upstream classes?
- Which current response keys are renamed, nested differently or typed
  differently?
- Which current methods are absent upstream?
- Which upstream methods mutate account/order state and must not be smoke-tested
  without explicit approval?
- Does upstream return binary documents as `UploadedFileInterface`, and can the
  Joomla wrapper expose the same data currently returned by `getBinary()`?

Output:

- `.pf/artifacts/worker-lapaygroup-data-parity-risk-matrix-20260811.md`

### Worker T11 - Reviewer

Model:

- `gpt-5.5`

Reasoning effort:

- `high`

Reason:

- The review is not a mechanical checklist. It must evaluate architecture,
  Joomla-way compliance, PHP baseline impact, live API safety and data-loss
  risk before a possible SDK migration.

Goal:

- Review the test plan and worker outputs before any product-code migration is
  proposed.

Review scope:

- Validate that no product code changes are required or allowed by the test
  tasks.
- Validate that the transport prototype uses `Joomla\Http\Http` as PSR-18 and
  Laminas Diactoros factories as PSR-17.
- Validate that Symfony HTTP Client is not used as the target path.
- Validate that live API tests are read-only unless explicitly approved.
- Validate that artifacts cannot leak credentials or raw auth headers.
- Validate that the parity matrix covers every public API surface currently used
  by this Joomla package.
- Validate that the PHP 8.3 requirement of `lapaygroup/russianpost` 2.0.0 is
  treated as a release decision gate, not a hidden implementation detail.

Output:

- `.pf/artifacts/reviewer-lapaygroup-test-plan-review-20260811.md`

## Acceptance Criteria

The upstream SDK is a viable replacement only if all are true:

- It installs in an isolated scratch area under PHP 8.3 on `joomla.local`.
- It works with `Joomla\Http\Http` as PSR-18 client.
- It works with Laminas Diactoros factories already present in Joomla.
- It can execute read-only real API calls with Joomla plugin credentials.
- It can return equivalent data for settings, shipping points, postoffice data
  and tariff calculation.
- No credential leakage appears in worker artifacts.
- A parity matrix shows no blocking data-loss risk for the public methods this
  Joomla package promises.

## Decision Gates

Gate 1:

- Dependency and bootstrap proof passes.
- If it fails, stop. Do not test live API.

Gate 2:

- Read-only runtime smoke passes.
- If it fails, classify failure as dependency, credential, transport, API or
  upstream SDK mismatch.

Gate 3:

- Data parity matrix is acceptable.
- Only after this gate should product-code migration be proposed.

Gate 4:

- Reviewer signs off the test evidence and explicitly classifies migration as
  `safe`, `unsafe`, or `needs-more-proof`.

## Expected Final Orchestrator Decision

Possible outcomes:

- `adopt-upstream-now`: safe only if PHP 8.3+ is accepted as the product
  minimum and parity passes.
- `keep-fork-temporarily`: required if PHP 8.1 support remains mandatory.
- `hybrid-wrapper`: use upstream for read-only/reference flows first, keep
  current implementation for mutating order/batch flows until parity is proven.
