# Templates

Все обязательные артефакты и журналы имеют готовые шаблоны.

## Rules

- не удалять секции
- помечать неприменимые секции как `Not applicable` с причиной
- использовать один шаблон на один artifact
- ссылаться на инструменты через logical tool names
- не использовать absolute paths to shared tools
- разрешать tool path через tool policy и toolchain contract
- использовать формулировку `run <logical-tool> via configured toolchain`

## Tool Reference Semantics

- logical tool name = WHAT should run
- resolves_to = WHERE runtime resolves the tool inside shared layers
- execution.strategy = HOW runtime invokes the tool
