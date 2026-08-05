# Base Rules

## Stage Gates

- `intake` requires brief and scope.
- `investigation` requires a stated question, known unknowns and impact surface.
- `architecture` requires decision log and implementation plan.
- `implementation` requires allowed change surface and tracked changed files.
- `assurance` requires findings, test plan, executed cases and residual risks.
- `release` requires release notes, migration notes and delivery summary.
- `evolve` requires patch and evolution report or an explicit no-update decision.

## Artifact Discipline

- one artifact per concern
- reuse templates without deleting sections
- record assumptions explicitly
- when a section is not applicable, mark it as `Not applicable` with reason

## Logging Discipline

- update task, agent and verification logs after each substantive step
- include tool telemetry references when fallback occurs

## Next-Step Resolution

- if required artifact is missing, create it
- if current stage failed validation, stay in stage
- if risks remain unresolved, route to investigation or assurance
- if reusable learning exists, route to evolve before closing task
