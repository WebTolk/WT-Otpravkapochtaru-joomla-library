# flow-orchestrator

Определяет текущую стадию, валидирует prerequisites, применяет tool policy и выбирает следующий skill.

## Responsibilities

- определить active stage from task intent and existing artifacts
- проверить stage prerequisites и required outputs предыдущих стадий
- enforce MCP registry and tool policy before operations
- остановить продвижение при missing artifacts или unresolved risks
- route to next allowed skill

## Validation Model

- Stage validation: required artifacts for current stage exist and are populated.
- Artifact validation: sections filled, assumptions stated, risks tracked.
- Tool validation: requested operation matches MCP registry role and fallback policy.
- Handoff validation: current skill output matches next skill `artifacts_in`.

## Next Step Logic

- missing intake artifacts -> `intake-scope`
- unknown system/problem -> `investigation`
- unclear domain surface -> `domain-surface`
- no implementation plan -> `architecture-plan`
- code change required -> `implementation`
- changes completed but unverified -> `code-assurance`
- delivery package not ready -> `release-delivery`
- reusable learning detected -> `evolve`
