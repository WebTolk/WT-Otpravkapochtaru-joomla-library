# Evolutions

`evolutions/` хранит state learning loop.

## Files

- `cursor.json` текущая позиция evolution pipeline
- `patch-cursor.template.json` структура cursor

## Loop

- patch records candidate reusable knowledge
- evolve validates stability and scope
- rules/templates/extensions/config update only when learning applies beyond one task
