<?php

declare(strict_types=1);

namespace Webtolk\Tests\Unit\Architecture;

use PHPUnit\Framework\TestCase;

final class ThinWrapperContractTest extends TestCase
{
    private const REQUIRED_SDK = 'lapaygroup/russianpost';

    private const REQUIRED_SOAP = 'ext-soap';
    private const REQUIRED_ZIP = 'ext-zip';

    private const RUNTIME_EXTENSIONS_REQUIRED = ['mbstring'];

    private const TARGET_ROOT_DOCS = [
        'README.md',
        'docs/README.md',
        'docs/thin-wrapper-architecture.md',
    ];

    private const REMOVED_API_PATTERNS = [
        'Webtolk\\Pochtaru\\',
        'Webtolk\\Otpravkapochtaru\\Configuration\\CredentialsProvider',
        'Webtolk\\Otpravkapochtaru\\Request',
        'Webtolk\\Otpravkapochtaru\\Exception\\OtpravkapochtaruException',
        'Webtolk\\Otpravkapochtaru\\SoapRequest',
        'Webtolk\\Otpravkapochtaru\\TrackingEntity',
        'Webtolk\\Otpravkapochtaru\\Dictionaries\\CountryDictionary',
        'Entity\\Order::fromArray(',
    ];

    public function testComposerRequiresUpstreamSdkAndSoapAndZipExtension(): void
    {
        $composer = $this->readJsonFile($this->projectPath('composer.json'));
        $requires = $composer['require'] ?? [];

        self::assertArrayHasKey(self::REQUIRED_SDK, $requires);
        self::assertArrayHasKey(self::REQUIRED_SOAP, $requires);
        self::assertArrayHasKey(self::REQUIRED_ZIP, $requires);
        self::assertNotSame('', (string) $requires[self::REQUIRED_SOAP]);
        self::assertNotSame('', (string) $requires[self::REQUIRED_ZIP]);
    }

    public function testGithubReleasePackageVersionFallsBackToProjectConfigNotUpstreamLock(): void
    {
        $packageConfig = $this->readJsonFile($this->projectPath('.dist/build/package.config.json'));
        self::assertNotSame('', trim((string) ($packageConfig['version'] ?? '')));

        $releaseScript = file_get_contents($this->projectPath('build/release.php'));
        self::assertIsString($releaseScript);
        self::assertStringContainsString('DEFAULT_PACKAGE_CONFIG', $releaseScript);
        self::assertStringContainsString('resolvePackageConfigVersion', $releaseScript);
        self::assertStringNotContainsString(
            "resolveDeployVersion(\n\t\t\ttrim((string) (\$options['version'] ?? '')),\n\t\t\t\$metadata['version']",
            $releaseScript,
            'Joomla package version must not fall back to the upstream SDK version from composer.lock.'
        );
        self::assertStringNotContainsString(
            "resolveDeployVersion((string) \$deployVersion, \$metadata['version'])",
            $releaseScript,
            'Metadata env export must not fall back to the upstream SDK version.'
        );

        $workflow = file_get_contents($this->projectPath('.github/workflows/release.yml'));
        self::assertIsString($workflow);
        self::assertStringContainsString('.dist/build/package.config.json', $workflow);
        self::assertStringNotContainsString('version from lockfile is used', $workflow);
    }

    public function testInstallerRequiredExtensionsDoNotHardFailSoap(): void
    {
        $script = file_get_contents($this->projectPath('script.php'));
        self::assertIsString($script);

        $match = [];
        self::assertTrue(
            preg_match('/protected array \$requiredPhpExtensions\s*=\s*\[(.*?)]\s*;/s', $script, $match) === 1,
            'Failed to parse requiredPhpExtensions declaration in script.php.'
        );

        $extensionBlock = $match[1] ?? '';
        $extensions = $this->parseArrayLiteralItems($extensionBlock);

        foreach (self::RUNTIME_EXTENSIONS_REQUIRED as $required) {
            self::assertContains($required, $extensions);
        }

        self::assertNotContains('soap', $extensions);
        self::assertNotContains('ext-soap', $extensions);
    }

    public function testReleaseZipContainsSdkAutoloadAndVendorSource(): void
    {
        $archives = glob($this->projectPath('dist/WT-Otpravkapochtaru-Joomla-library_*.zip'));
        if ($archives === false || $archives === []) {
            self::markTestSkipped('No thin-wrapper release archive found in dist/.');
        }

        rsort($archives);
        $archivePath = (string) $archives[0];
        $zip = new \ZipArchive();
        self::assertTrue($zip->open($archivePath) === true, 'Failed to open release archive.');

        $entries = [];
        for ($index = 0, $count = $zip->numFiles; $index < $count; $index++) {
            $name = $zip->getNameIndex($index);
            if ($name !== false) {
                $entries[] = str_replace('\\\\', '/', (string) $name);
            }
        }

        $zip->close();

        self::assertContains(
            'lib_webtolk_otpravkapochtaru/src/libraries/vendor/autoload.php',
            $entries
        );
        self::assertTrue(
            $this->hasEntryWithPrefix(
                $entries,
                'lib_webtolk_otpravkapochtaru/src/libraries/vendor/lapaygroup/russianpost/src/'
            ),
            'SDK runtime source is not present in release package.'
        );
    }

    public function testRuntimeSourceAndPublicDocsDoNotReferenceRemovedForkAPIs(): void
    {
        $files = [];

        $files[] = $this->projectPath('lib_webtolk_otpravkapochtaru/src');

        foreach (self::TARGET_ROOT_DOCS as $path) {
            $files[] = $this->projectPath($path);
        }

        $violations = $this->findViolations($files);
        self::assertSame(
            [],
            $violations,
            sprintf(
                'Found references to removed fork APIs in active runtime/docs surface: %s',
                implode('; ', $violations)
            )
        );
    }

    private function hasEntryWithPrefix(array $entries, string $prefix): bool
    {
        foreach ($entries as $entry) {
            if (str_starts_with($entry, $prefix)) {
                return true;
            }
        }

        return false;
    }

    private function findViolations(array $files): array
    {
        $violations = [];

        foreach ($files as $file) {
            if (is_dir($file)) {
                foreach ($this->phpFiles($file) as $phpFile) {
                    $violations = array_merge($violations, $this->scanFile((string) $phpFile));
                }

                continue;
            }

            $violations = array_merge($violations, $this->scanFile($file));
        }

        return array_values(array_unique($violations));
    }

    private function scanFile(string $file): array
    {
        if (!is_file($file) || !is_readable($file)) {
            return [];
        }

        $contents = file_get_contents($file);
        if (!is_string($contents)) {
            return [];
        }

        $fileViolations = [];
        foreach (self::REMOVED_API_PATTERNS as $forbidden) {
            if (str_contains($contents, $forbidden)) {
                $fileViolations[] = sprintf('%s (%s)', $file, $forbidden);
                break;
            }
        }

        return $fileViolations;
    }

    private function parseArrayLiteralItems(string $block): array
    {
        $extensions = [];
        preg_match_all('/[\'"]([^\'"]+)[\'"]/', $block, $matches);

        foreach ($matches[1] as $extension) {
            $extensions[] = $extension;
        }

        return $extensions;
    }

    private function phpFiles(string $directory): iterable
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $item) {
            if (!$item->isFile()) {
                continue;
            }

            if (strtolower((string) $item->getExtension()) !== 'php') {
                continue;
            }

            yield $item->getPathname();
        }
    }

    private function readJsonFile(string $path): array
    {
        $contents = file_get_contents($path);
        self::assertIsString($contents);

        $decoded = json_decode($contents, true);
        self::assertIsArray($decoded);

        return $decoded;
    }

    private function projectPath(string $relative): string
    {
        return dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . $relative;
    }
}
