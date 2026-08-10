# Otpravka API Knowledge Package Connection

## Metadata

- created_at: 2026-08-06T08:25:47+04:00
- package_id: `docs.api.otpravka-pochta`
- scope: ProcessForge project context
- product_code_changed: no

## Result

The project now extends its ProcessForge context with the workplace knowledge package `docs.api.otpravka-pochta`.

The package is a workplace documentation package for the Russian Post Otpravka REST API local mirror. Its root resource resolves through the workplace `knowledge_roots` registry to the local documentation mirror for `rest-api/otpravka-pochta`.

## Changes Applied

- Created `.pf/project-overrides.yaml` with a `knowledge_packages` extension override for `docs.api.otpravka-pochta`.
- Refreshed the project context snapshot.
- Sanitized generated workplace specialization paths in the refreshed YAML snapshot to keep public `.pf` files free of local absolute paths.
- Removed deprecated compatibility context files generated during inspection and not needed for the active project-context flow.

## Verification

Commands run:

- `knowledge-package-doctor --package docs.api.otpravka-pochta`
- `project-override-add --kind knowledge_package --target docs.api.otpravka-pochta --mode extension --apply`
- `project-context-refresh --project-root .`
- `project-context-check --project-root .`
- `doctor-project --project-root .`

Results:

- `knowledge-package-doctor`: pass, with non-blocking navigation warning.
- New snapshot: `ctx-20260806-042437-8f6d61`.
- `project-context-check`: fresh, continue.
- `doctor-project`: pass.
- `docs.api.otpravka-pochta` appears in effective resources and required knowledge resources.

Known retained warning:

- `doctor-project` still reports the existing runtime-access waiver for `filesystem.read` and `filesystem.write`.

## Next Use

Use `docs.api.otpravka-pochta` before designing or implementing:

- OPS list loading;
- shipment category filtering by OPS;
- shipment type filtering by selected category;
- AJAX endpoint request/response contracts for Otpravka-related select options.
