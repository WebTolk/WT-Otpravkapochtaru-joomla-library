# Quality Tooling Setup

## Scope
- Date: 2026-07-09
- Task: Configure code style, tests and quality checks through global PHP QA tools.

## Global Tool Roots
- PHP QA binaries: `D:/.agents/tools/php-qa/vendor/bin/`
- PHP QA fallback configs: `D:/.agents/tools/php-qa/config/`
- Joomla platform knowledge: `D:/.agents/platforms/joomla/platform.json`
- Joomla toolkit: `D:/.agents/docs/joomla-toolkit/`
- Joomla core scan/autoload source: `D:/.agents/docs/Joomla-core/6.x/6.1.0/`

## Project Entrypoints
- Composer metadata and scripts: `composer.json`
- Editor style: `.editorconfig`
- PHP CS Fixer config: `.php-cs-fixer.dist.php`
- PHPCS config: `phpcs.xml`
- PHPStan config: `phpstan.neon`
- PHPUnit config: `phpunit.xml`
- PHP lint helper: `tools/qa/lint-php.ps1`
- PHPUnit/PHPStan bootstrap: `tests/bootstrap.php`

## Commands
- Syntax: `php -l script.php; powershell -NoProfile -ExecutionPolicy Bypass -File tools/qa/lint-php.ps1`
- Tests: `php D:/.agents/tools/php-qa/vendor/bin/phpunit --configuration=phpunit.xml`
- Static analysis: `php D:/.agents/tools/php-qa/vendor/bin/phpstan analyse --configuration=phpstan.neon`
- Style dry-run: `php D:/.agents/tools/php-qa/vendor/bin/php-cs-fixer fix --dry-run --diff --config=.php-cs-fixer.dist.php`
- PHPCS: `php D:/.agents/tools/php-qa/vendor/bin/phpcs --standard=phpcs.xml`

## Verification Results
- PHP lint: passed for product PHP files and tests.
- PHPUnit: passed, `3 tests / 4 assertions`.
- PHPStan: passed, no errors.
- PHP CS Fixer: applied to full configured source set; follow-up dry-run passed.
- PHPCS: initial full run found `5` auto-fixable errors; PHPCBF fixed them; final PHPCS passed.

## 2026-07-09 Tool Application
- `php-cs-fixer fix --config=.php-cs-fixer.dist.php` applied formatting to `18` files.
- `phpcbf --standard=phpcs.xml` fixed `5` PHPCS violations in `3` files.
- Final gate status: PHP lint, PHPUnit, PHPStan, PHP CS Fixer dry-run, and PHPCS all pass through direct global binary invocation.

## Known Tooling Constraints
- Local `composer --version` fails in this shell with `Could not open input file: \composer.phar`; direct global binary invocations were used for verification.
- PHP CS Fixer runs under PHP `8.3.30` while `composer.json` declares platform PHP `8.1.0`, so the tool warns about possible PHP-version mismatch.
- Composer script aliases are configured but were not used as verification truth in this shell.
