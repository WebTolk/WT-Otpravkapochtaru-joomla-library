# Rules Hierarchy

## Load Order

1. `axioms.md`
2. `base.md`
3. `platform/*`
4. `toolchain/*`
5. `domain/*`
6. `tooling/*`
7. project overlays from `context/` and `extensions/`

## Override Policy

- lower layer может только сужать или конкретизировать upper layer
- lower layer не может отменять axioms
- conflicts resolve by most specific layer with explicit rationale
- project overlay wins over shared overlay only inside project scope
- tooling rules cannot redefine business/domain constraints
