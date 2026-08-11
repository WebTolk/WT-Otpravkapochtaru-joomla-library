# Worker Launch Prompt

You are a Process Forge shell-worker.
Requested model: `gpt-5.3-codex-spark`.
You are not the orchestrator.

## Assignment

- task_id: `t22-wrapper-package-strategy`
- run_id: `lapaygroup-thin-wrapper-migration-20260811`
- mode: read-only planning

## One Job

Define the minimal Joomla wrapper and packaging/autoload strategy for using
`lapaygroup/russianpost` 2.0.0 in this extension package.

The packaging strategy must follow the current WT Max Joomla library pattern:
Composer refresh on GitHub Actions release, package metadata from
`composer.lock`, runtime SDK copy into the Joomla library tree, and a
package-local SDK autoloader.

## Read Scope

- package manifests and build config
- current library manifest
- `script.php`
- `.pf/artifacts/worker-lapaygroup-core-swap-writer-20260811.md`
- `.pf/artifacts/worker-lapaygroup-local-sdk-inspection-20260811.md`
- `.pf/artifacts/wt-max-composer-build-reference-20260811.md`
- local SDK `composer.json`
- WT Max reference files, if network access is available:
  - `https://github.com/WebTolk/WT-Max-Joomla-library/blob/main/composer.json`
  - `https://github.com/WebTolk/WT-Max-Joomla-library/blob/main/.github/workflows/release.yml`
  - `https://github.com/WebTolk/WT-Max-Joomla-library/blob/main/build/release.php`

## Rules

- Do not change files.
- Do not design old public facade compatibility.
- Do not put JoomShopping integration in the library.
- Do not design a release process that vendors a stale fixed SDK snapshot by
  hand.

## Output

Write `.pf/artifacts/worker-thin-wrapper-package-strategy-20260811.md` with:

- how the SDK should be refreshed by Composer during GitHub release builds
- how the SDK should be shipped/autoloaded inside the Joomla package
- how package version/date should be derived from Composer metadata and manual
  overrides
- minimal wrapper services/classes to keep in our namespace
- installer/update responsibilities
- rollback risks
- files likely touched by the implementation writer later
