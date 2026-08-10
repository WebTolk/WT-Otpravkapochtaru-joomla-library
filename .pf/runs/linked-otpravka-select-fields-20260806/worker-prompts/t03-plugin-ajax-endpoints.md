# Worker Launch Prompt

You are a worker agent.
You are not the orchestrator.
Use only the assigned task and the provided assignment capsule.
Do not rebuild full project context unless explicitly allowed.
Do not edit files outside allowed_files.
Do not read files outside allowed_read_files unless explicitly allowed.
Use workspace_access_file for explicitly granted workplace resources.
Do not copy private paths from workspace_access_file into public project artifacts, assignments, capsules, or reports.
Respect forbidden_files.
Produce required_outputs.
Write expected_report.
Stop and report if scope is insufficient.
Invoke subagents only when subagent_policy.allow is true.
When subagent reports are required, write them only under subagent_policy.reports_dir.

## Assignment

- task_id: `t03-plugin-ajax-endpoints`
- run_id: `linked-otpravka-select-fields-20260806`
- assignment: `.pf/assignments/t03-plugin-ajax-endpoints.yaml`
- capsule: `.pf/contexts/assignment-capsules/t03-plugin-ajax-endpoints.capsule.yaml`
- workspace_access_file: `.pf/runtime/agent-runs/linked-otpravka-select-fields-20260806/t03-plugin-ajax-endpoints/workspace-access.json`
- worker_may_rebuild_context: `false`

## workspace_access

- knowledge_resources: `2`
- templates: `0`
- tools: `1`
- mcp: `2`

## allowed_files

- `plg_system_wt_otpravkapochtaru/src/Extension/*`
- `plg_system_wt_otpravkapochtaru/src/Service/*`
- `plg_system_wt_otpravkapochtaru/language/*/*.ini`
- `tests/Unit/PluginAjax/*`

## allowed_read_files

- `.pf/artifacts/linked-select-fields-orchestration-plan-20260806.md`
- `.pf/artifacts/category-type-source-contract-20260806.md`
- `.pf/artifacts/legacy-linked-select-fields-investigation-20260806.md`
- `.pf/artifacts/worker-api-security-design-v2-review-20260806.md`
- `.pf/artifacts/worker-task-briefs-linked-select-fields-20260806.md`
- `.pf/artifacts/worker-orchestration-rules-20260806.md`
- `lib_webtolk_otpravkapochtaru/src/Otpravkapochtaru.php`
- `lib_webtolk_otpravkapochtaru/src/Fields/OpslistField.php`
- `plg_system_wt_otpravkapochtaru/src/Extension/WtOtpravkapochtaru.php`
- `plg_system_wt_otpravkapochtaru/services/provider.php`
- `plg_system_wt_otpravkapochtaru/language/en-GB/plg_system_wt_otpravkapochtaru.ini`
- `plg_system_wt_otpravkapochtaru/language/ru-RU/plg_system_wt_otpravkapochtaru.ini`

## forbidden_files

- `lib_webtolk_otpravkapochtaru/src/Fields/*`
- `plg_system_wt_otpravkapochtaru/media/*`

## required_outputs

- `changed_files` -> `.pf/artifacts/worker-plugin-ajax-report-20260806.md`
- `security_notes` -> `.pf/artifacts/worker-plugin-ajax-report-20260806.md`
- `unit_tests` -> `.pf/artifacts/worker-plugin-ajax-report-20260806.md`

## expected_report

- `.pf/artifacts/worker-plugin-ajax-report-20260806.md`

## subagent_policy

- allow: `false`
- max_subagents: `0`
- require_reports: `false`
- reports_dir: ``
