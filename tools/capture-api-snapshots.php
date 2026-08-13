<?php

declare(strict_types=1);

/**
 * Capture real JSON response snapshots for the WT Otpravkapochtaru facade.
 *
 * Run from the repository root:
 *
 * php tools/capture-api-snapshots.php --joomla-root=/path/to/joomla
 *
 * By default the script uses safe sample inputs for mutating methods. Those
 * calls still hit the facade and usually capture real validation/API errors,
 * but they avoid intentionally creating, deleting, or changing shipments.
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\Session\SessionInterface;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

const SNAPSHOT_SCHEMA_VERSION = 1;

$options = parseOptions($argv);
$repoRoot = dirname(__DIR__);
$joomlaRootOption = $options['joomla-root'] ?? getenv('JOOMLA_ROOT') ?: null;

if (!is_string($joomlaRootOption) || trim($joomlaRootOption) === '') {
    fwrite(STDERR, 'Usage: php tools/capture-api-snapshots.php --joomla-root=<path-to-joomla-root> [--output-dir=<path>] [--method=<method>]' . PHP_EOL);
    exit(2);
}

$joomlaRoot = normalizePath($joomlaRootOption);
$outputDir = normalizePath((string) ($options['output-dir'] ?? $repoRoot . '/docs/api-snapshots/latest'));
$methodFilter = isset($options['method']) ? (string) $options['method'] : null;
$includeLiveMutating = isset($options['include-live-mutating']);

bootstrapJoomla($joomlaRoot);
registerLibraryAutoloaders($joomlaRoot);

$client = new Otpravkapochtaru();
$cases = buildCases($includeLiveMutating);

if (!is_dir($outputDir) && !mkdir($outputDir, 0775, true) && !is_dir($outputDir)) {
    fwrite(STDERR, 'Failed to create output directory: ' . $outputDir . PHP_EOL);
    exit(1);
}

$index = [
    'schema_version' => SNAPSHOT_SCHEMA_VERSION,
    'captured_at' => gmdate('c'),
    'runtime' => [
        'php' => PHP_VERSION,
        'joomla_root' => '[local-path-redacted]',
        'side_effect_mode' => $includeLiveMutating ? 'live-mutating-enabled' : 'safe-error-inputs',
    ],
    'methods' => [],
];

foreach ($cases as $case) {
    if ($methodFilter !== null && $case['method'] !== $methodFilter) {
        continue;
    }

    $snapshot = captureCase($client, $case);
    $fileName = methodFileName($case['method']) . '.json';
    writeJson($outputDir . '/' . $fileName, $snapshot);

    $index['methods'][] = [
        'method' => $case['method'],
        'file' => $fileName,
        'group' => $case['group'],
        'side_effects' => $case['side_effects'],
        'status' => $snapshot['status'],
        'duration_ms' => $snapshot['duration_ms'],
    ];

    echo $case['method'] . ': ' . $snapshot['status'] . ' -> ' . $fileName . PHP_EOL;
}

if ($methodFilter === null) {
    writeJson($outputDir . '/index.json', $index);
} else {
    writeJson($outputDir . '/' . methodFileName($methodFilter) . '.index.json', $index);
}

function parseOptions(array $argv): array
{
    $result = [];

    foreach (array_slice($argv, 1) as $argument) {
        if (!str_starts_with($argument, '--')) {
            continue;
        }

        $argument = substr($argument, 2);

        if (str_contains($argument, '=')) {
            [$key, $value] = explode('=', $argument, 2);
            $result[$key] = $value;
        } else {
            $result[$argument] = true;
        }
    }

    return $result;
}

function normalizePath(string $path): string
{
    return str_replace('\\', '/', rtrim($path, "\\/"));
}

function bootstrapJoomla(string $joomlaRoot): void
{
    if (!is_file($joomlaRoot . '/includes/defines.php') || !is_file($joomlaRoot . '/includes/framework.php')) {
        fwrite(STDERR, 'Invalid Joomla root: ' . $joomlaRoot . PHP_EOL);
        exit(1);
    }

    defined('_JEXEC') || define('_JEXEC', 1);
    defined('JOOMLA_MINIMUM_PHP') || define('JOOMLA_MINIMUM_PHP', '8.3.0');
    defined('JPATH_BASE') || define('JPATH_BASE', $joomlaRoot);

    require_once $joomlaRoot . '/includes/defines.php';
    require_once $joomlaRoot . '/includes/framework.php';

    $container = Factory::getContainer();
    $container->alias('session', 'session.cli')
        ->alias('JSession', 'session.cli')
        ->alias(Session::class, 'session.cli')
        ->alias(\Joomla\Session\Session::class, 'session.cli')
        ->alias(SessionInterface::class, 'session.cli');

    Factory::$application = $container->get(\Joomla\Console\Application::class);
}

function registerLibraryAutoloaders(string $joomlaRoot): void
{
    $libraryRoot = $joomlaRoot . '/libraries/Webtolk/Otpravkapochtaru/src';
    $vendorAutoload = $libraryRoot . '/libraries/vendor/autoload.php';

    if (is_file($vendorAutoload)) {
        require_once $vendorAutoload;
    }

    spl_autoload_register(static function (string $class) use ($libraryRoot): void {
        $prefix = 'Webtolk\\Otpravkapochtaru\\';

        if (!str_starts_with($class, $prefix)) {
            return;
        }

        $relativePath = str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        $file = $libraryRoot . '/' . $relativePath;

        if (is_file($file)) {
            require_once $file;
        }
    });
}

function buildCases(bool $includeLiveMutating): array
{
    $rpo = preg_replace('/\D+/', '', (string) (getenv('WT_OTPRAVKA_SNAPSHOT_RPO') ?: '80214523462306')) ?: '80214523462306';
    $orderId = (string) (getenv('WT_OTPRAVKA_SNAPSHOT_ORDER_ID') ?: '0');
    $shopId = (string) (getenv('WT_OTPRAVKA_SNAPSHOT_SHOP_ID') ?: 'docs-nonexistent-' . gmdate('Ymd'));
    $batchName = (string) (getenv('WT_OTPRAVKA_SNAPSHOT_BATCH') ?: 'DOCS-NONEXISTENT-BATCH');
    $ticket = (string) (getenv('WT_OTPRAVKA_SNAPSHOT_TICKET') ?: 'DOCS-NONEXISTENT-TICKET');

    $orderPayload = $includeLiveMutating ? sampleOrderPayload() : [[]];
    $returnShipmentPayload = $includeLiveMutating ? sampleReturnShipmentPayload() : [[]];

    return [
        caseDef('account', 'getAccountInfo', 'read', []),
        caseDef('account', 'getShippingPoints', 'read', []),
        caseDef('account', 'getApiLimit', 'read', []),
        caseDef('account', 'getSettings', 'read', []),
        caseDef('orders', 'createOrders', $includeLiveMutating ? 'mutating' : 'safe-error', [$orderPayload]),
        caseDef('orders', 'editOrder', 'safe-error', [sampleOrderPayload(), $orderId]),
        caseDef('orders', 'findOrderById', 'read', [$orderId]),
        caseDef('orders', 'findOrderByShopId', 'read', [$shopId]),
        caseDef('orders', 'findOrderByRpo', 'read', [$rpo]),
        caseDef('orders', 'getRecipientReliability', 'read', [sampleRecipientPayload()]),
        caseDef('orders', 'getRecipientsReliability', 'read', [[sampleRecipientPayload()]]),
        caseDef('orders', 'deleteOrders', 'safe-error', [[0]]),
        caseDef('orders', 'returnOrdersToNew', 'safe-error', [[0]]),
        caseDef('batches', 'createBatch', 'safe-error', [[0], null, false]),
        caseDef('batches', 'getAllBatches', 'read', []),
        caseDef('batches', 'getOrdersInBatch', 'read', [$batchName]),
        caseDef('documents', 'generateDocumentPackage', 'safe-error', [$batchName, 'paper', 'one-sided']),
        caseDef('documents', 'generateDocumentF103', 'safe-error', [$batchName]),
        caseDef('returns', 'createReturnShipment', 'safe-error', ['00000000000000', 'UNDEFINED']),
        caseDef('returns', 'createReturnShipments', $includeLiveMutating ? 'mutating' : 'safe-error', [$returnShipmentPayload]),
        caseDef('returns', 'editReturnShipment', 'safe-error', [sampleReturnShipmentPayload()[0], '00000000000000']),
        caseDef('returns', 'deleteReturnShipment', 'safe-error', ['00000000000000']),
        caseDef('tariffs', 'getTariff', 'read', [27030, sampleTariffPayload()]),
        caseDef('tariffs', 'getTariffAndDeliveryPeriod', 'read', [27030, sampleTariffPayload()]),
        caseDef('dictionaries', 'getCountryList', 'read', []),
        caseDef('post-offices', 'searchPostOfficeByIndex', 'read', ['410012']),
        caseDef('post-offices', 'searchPostOfficeByAddress', 'read', ['Саратов, Московская, 109', 3]),
        caseDef('post-offices', 'searchPostOfficeByCoordinates', 'read', [['latitude' => '51.533557', 'longitude' => '46.034257', 'top' => 3]]),
        caseDef('post-offices', 'getPostOfficeServices', 'read', ['410012']),
        caseDef('post-offices', 'getPostalCodesInLocality', 'read', ['Саратов', 'Саратовская область']),
        caseDef('tracking', 'getOperationsByRpo', 'tracking-read', [$rpo, 'RUS']),
        caseDef('tracking', 'getNpayInfo', 'tracking-read', [$rpo, 'RUS']),
        caseDef('tracking', 'getTickets', 'tracking-mutating', [[$rpo], 'RUS']),
        caseDef('tracking', 'getOperationsByTicket', 'tracking-read', [$ticket]),
    ];
}

function caseDef(string $group, string $method, string $sideEffects, array $arguments): array
{
    return [
        'group' => $group,
        'method' => $method,
        'side_effects' => $sideEffects,
        'arguments' => $arguments,
    ];
}

function captureCase(Otpravkapochtaru $client, array $case): array
{
    $startedAt = microtime(true);
    $snapshot = [
        'schema_version' => SNAPSHOT_SCHEMA_VERSION,
        'captured_at' => gmdate('c'),
        'method' => $case['method'],
        'group' => $case['group'],
        'side_effects' => $case['side_effects'],
        'input' => redact($case['arguments']),
        'status' => 'success',
        'duration_ms' => 0,
        'result' => null,
        'error' => null,
    ];

    try {
        $snapshot['result'] = redactAccountSnapshot(
            $case['method'],
            redact($client->{$case['method']}(...$case['arguments']))
        );
    } catch (Throwable $exception) {
        $snapshot['status'] = 'error';
        $snapshot['error'] = exceptionToArray($exception);
    } finally {
        $snapshot['duration_ms'] = (int) round((microtime(true) - $startedAt) * 1000);
    }

    return $snapshot;
}

function sampleTariffPayload(): array
{
    return [
        'from-index' => '410012',
        'to-index' => '455001',
        'mail-type' => 'POSTAL_PARCEL',
        'mail-category' => 'ORDINARY',
        'mass' => 1000,
    ];
}

function sampleRecipientPayload(): array
{
    return [
        'index-to' => '455001',
        'region-to' => 'Челябинская область',
        'place-to' => 'Магнитогорск',
        'street-to' => 'Ленина',
        'house-to' => '1',
        'recipient-name' => 'Иванов Иван',
        'tel-address' => '79000000000',
    ];
}

function sampleOrderPayload(): array
{
    return [
        [
            'order-num' => 'docs-' . gmdate('Ymd-His'),
            'recipient-name' => 'Иванов Иван',
            'tel-address' => '79000000000',
            'index-to' => '455001',
            'region-to' => 'Челябинская область',
            'place-to' => 'Магнитогорск',
            'street-to' => 'Ленина',
            'house-to' => '1',
            'mail-type' => 'POSTAL_PARCEL',
            'mail-category' => 'ORDINARY',
            'mass' => 1000,
        ],
    ];
}

function sampleReturnShipmentPayload(): array
{
    return [
        [
            'postoffice-code' => '410012',
            'address-from' => [
                'index' => '410012',
                'place' => 'Саратов',
                'street' => 'Московская',
                'house' => '109',
            ],
            'address-to' => [
                'index' => '455001',
                'place' => 'Магнитогорск',
                'street' => 'Ленина',
                'house' => '1',
            ],
        ],
    ];
}

function exceptionToArray(Throwable $exception): array
{
    $data = [
        'class' => $exception::class,
        'code' => $exception->getCode(),
        'message' => redact($exception->getMessage()),
    ];

    if ($exception->getPrevious() !== null) {
        $data['previous'] = [
            'class' => $exception->getPrevious()::class,
            'code' => $exception->getPrevious()->getCode(),
            'message' => redact($exception->getPrevious()->getMessage()),
        ];
    }

    return $data;
}

function redact(mixed $value): mixed
{
    if (is_array($value)) {
        $result = [];

        foreach ($value as $key => $item) {
            $keyString = is_string($key) ? $key : (string) $key;
            $result[$key] = preg_match('/token|password|login|auth|secret|email|phone|tel-address|recipient-name|sender-name|org-inn|org-kpp|org-name|legal-hid|admin-hid|^hid$|agreement-number/i', $keyString)
                ? '[redacted]'
                : redact($item);
        }

        return $result;
    }

    if (is_object($value)) {
        return redact(json_decode(json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), true));
    }

    if (is_string($value)) {
        $value = preg_replace('/(AccessToken|Authorization|X-User-Authorization|Password|Login|Email|Phone|Inn|Hid|AgreementNumber)(\\s*[:=]\\s*)[^\\s,;]+/i', '$1$2[redacted]', $value) ?? $value;
        $value = preg_replace('/Bearer\\s+[A-Za-z0-9._~+\\/-]+=*/i', 'Bearer [redacted]', $value) ?? $value;
    }

    return $value;
}

function redactAccountSnapshot(string $method, mixed $value): mixed
{
    if (!in_array($method, ['getAccountInfo', 'getSettings'], true) || !is_array($value)) {
        return $value;
    }

    if (array_key_exists('address', $value)) {
        $value['address'] = '[redacted]';
    }

    return $value;
}

function methodFileName(string $method): string
{
    return strtolower((string) preg_replace('/(?<!^)[A-Z]/', '-$0', $method));
}

function writeJson(string $path, array $data): void
{
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    if ($json === false) {
        throw new RuntimeException('Failed to encode JSON for ' . $path . ': ' . json_last_error_msg());
    }

    file_put_contents($path, $json . PHP_EOL);
}
