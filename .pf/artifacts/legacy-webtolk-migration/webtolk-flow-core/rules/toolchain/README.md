# Toolchain Rules

Toolchain overlays define build, lint, test, static analysis and packaging expectations.

## Contents

- commands and quality gates
- formatting/lint rules
- package/build expectations
- logical tool name to relative path mapping
- execution contracts resolved against runtime base path

## Runtime Resolution

- runtime knows the shared `.webtolk` base path
- toolchain contracts store relative locations only
- templates refer to logical tool names and the selected toolchain contract
- tool invocation format: `run <logical-tool> via configured toolchain`
- logical tool name = WHAT
- resolves_to = WHERE
- execution.strategy = HOW

## Override Boundary

- may add mandatory checks
- may define command routing
- may not inject language assumptions into core skill contracts
