# Extensions

Extensions add platform, toolchain, domain, tooling or project overlays without editing core flow.

## Rules

- each extension should provide a manifest compatible with `config/extension.schema.json`
- extensions load after base rules and before project-local overrides
- extension files may add rules, templates, scripts or docs
- extension must not override axioms

## Example Structure

- `manifest.yaml`
- `rules/*.md`
- `templates/*.md`
- `docs/*.md`
