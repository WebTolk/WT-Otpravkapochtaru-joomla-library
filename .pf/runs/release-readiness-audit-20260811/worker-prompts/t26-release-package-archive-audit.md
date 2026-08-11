# Worker Launch Prompt

You are a Process Forge shell-worker.
Requested model: `gpt-5.3-codex-spark`.
You are not the orchestrator.

## Assignment

- task_id: `t26-release-package-archive-audit`
- run_id: `release-readiness-audit-20260811`
- mode: read-only product audit
- workspace_access_file: `.pf/runtime/agent-runs/release-readiness-audit-20260811/t26-release-package-archive-audit/workspace-access.json`

## One Job

Inspect the current release ZIP and package manifests. Do not change product
code. Do not rebuild unless `.packages/WT Otpravkapochtaru_3.0.0.zip` is
missing.

## Read Scope

- `.packages/WT Otpravkapochtaru_3.0.0.zip`
- `pkg_lib_wt_otpravkapochtaru.xml`
- `lib_webtolk_otpravkapochtaru/otpravkapochtaru.xml`
- `plg_system_wt_otpravkapochtaru/wtotpravkapochtaru.xml`
- `lib_webtolk_otpravkapochtaru/media/joomla.asset.json`
- `lib_webtolk_otpravkapochtaru/media/js/linked-select-fields.js`

## Checks

- ZIP exists and report size and entry count.
- ZIP contains `script.php`.
- ZIP contains library media `lib_webtolk_otpravkapochtaru/media/joomla.asset.json`.
- ZIP contains library JS under `lib_webtolk_otpravkapochtaru/media/js/`.
- ZIP does not contain old plugin-owned linked-select media entries.
- Library manifest installs media to `lib_wt_otpravkapochtaru`.
- Plugin manifest does not install linked-select media.

## Output

Write only `.pf/artifacts/worker-release-package-archive-audit-20260811.md`.
Use ASCII-only English text. Include verdict: `pass`, `needs-fix`, or `blocked`,
commands/evidence, and any residual risk.
