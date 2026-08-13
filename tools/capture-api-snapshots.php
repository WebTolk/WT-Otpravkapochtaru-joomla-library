<?php

/**
 * Capture real JSON response snapshots through the thin Joomla facade.
 *
 * Run from the repository root:
 *
 * php tools/capture-api-snapshots.php --joomla-root=/path/to/joomla
 */

declare(strict_types=1);

use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\Session\SessionInterface;
use LapayGroup\RussianPost\Entity\AddressReturn;
use LapayGroup\RussianPost\Entity\Order;
use LapayGroup\RussianPost\Entity\Recipient;
use LapayGroup\RussianPost\Entity\ReturnShipment;
use LapayGroup\RussianPost\Providers\OtpravkaApi;
use Psr\Http\Message\UploadedFileInterface;
use Webtolk\Otpravkapochtaru\Otpravkapochtaru;

const SNAPSHOT_SCHEMA_VERSION = 2;

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
    $fileName = methodFileName($case['target'] . '-' . $case['method']) . '.json';
    writeJson($outputDir . '/' . $fileName, $snapshot);

    $index['methods'][] = [
        'target' => $case['target'],
        'method' => $case['method'],
        'file' => $fileName,
        'group' => $case['group'],
        'side_effects' => $case['side_effects'],
        'status' => $snapshot['status'],
        'duration_ms' => $snapshot['duration_ms'],
    ];

    echo $case['target'] . '::' . $case['method'] . ': ' . $snapshot['status'] . ' -> ' . $fileName . PHP_EOL;
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
    $orderId = (int) (getenv('WT_OTPRAVKA_SNAPSHOT_ORDER_ID') ?: 0);
    $shopId = (string) (getenv('WT_OTPRAVKA_SNAPSHOT_SHOP_ID') ?: 'docs-nonexistent-' . gmdate('Ymd'));
    $batchName = (string) (getenv('WT_OTPRAVKA_SNAPSHOT_BATCH') ?: 'DOCS-NONEXISTENT-BATCH');
    $ticket = (string) (getenv('WT_OTPRAVKA_SNAPSHOT_TICKET') ?: 'DOCS-NONEXISTENT-TICKET');
    $orderPayload = $includeLiveMutating ? [sampleOrderEntity()->asArr()] : [[]];
    $returnPayload = $includeLiveMutating ? [sampleReturnShipmentEntity()->asArr()] : [[]];

    return [
        caseDef('facade', 'account', 'getAccountInfo', 'read', []),
        caseDef('facade', 'account', 'getApiLimit', 'read', []),
        caseDef('otpravka', 'account', 'settings', 'read', []),
        caseDef('otpravka', 'account', 'shippingPoints', 'read', []),
        caseDef('otpravka', 'account', 'getBalance', 'read', []),
        caseDef('otpravka', 'orders', 'createOrders', $includeLiveMutating ? 'mutating' : 'safe-error', [$orderPayload]),
        caseDef('otpravka', 'orders', 'createOrdersV2', $includeLiveMutating ? 'mutating' : 'safe-error', [$orderPayload]),
        caseDef('otpravka', 'orders', 'editOrder', 'safe-error', [sampleOrderEntity(), $orderId]),
        caseDef('otpravka', 'orders', 'findOrderById', 'read', [$orderId]),
        caseDef('otpravka', 'orders', 'findOrderByShopId', 'read', [$shopId]),
        caseDef('otpravka', 'orders', 'findOrderByRpo', 'read', [$rpo]),
        caseDef('otpravka', 'orders', 'findOrderInBatch', 'read', [$orderId]),
        caseDef('otpravka', 'orders', 'untrustworthyRecipient', 'read', [sampleRecipientEntity()]),
        caseDef('otpravka', 'orders', 'untrustworthyRecipients', 'read', [[sampleRecipientEntity()]]),
        caseDef('otpravka', 'orders', 'deleteOrders', 'safe-error', [[0]]),
        caseDef('otpravka', 'orders', 'returnToNew', 'safe-error', [[0]]),
        caseDef('otpravka', 'batches', 'createBatch', 'safe-error', [[0], null, false]),
        caseDef('otpravka', 'batches', 'getAllBatches', 'read', []),
        caseDef('otpravka', 'batches', 'moveOrdersToBatch', 'safe-error', [$batchName, [0]]),
        caseDef('otpravka', 'batches', 'findBatchByName', 'read', [$batchName]),
        caseDef('otpravka', 'batches', 'addOrdersToBatch', 'safe-error', [$batchName, $orderPayload]),
        caseDef('otpravka', 'batches', 'deleteOrdersInBatch', 'safe-error', [[0]]),
        caseDef('otpravka', 'batches', 'getOrdersInBatch', 'read', [$batchName]),
        caseDef('otpravka', 'batches', 'changeBatchSendingDay', 'safe-error', [$batchName, new DateTimeImmutable('tomorrow')]),
        caseDef('otpravka', 'batches', 'getArchivedBatches', 'read', []),
        caseDef('otpravka', 'batches', 'archivingBatch', 'safe-error', [[$batchName]]),
        caseDef('otpravka', 'batches', 'unarchivingBatch', 'safe-error', [[$batchName]]),
        caseDef('otpravka', 'documents', 'generateDocPackage', 'safe-error', [$batchName, OtpravkaApi::PRINT_FILE]),
        caseDef('otpravka', 'documents', 'generateDocF103', 'safe-error', [$batchName, OtpravkaApi::PRINT_FILE]),
        caseDef('otpravka', 'documents', 'generateReturnLabel', 'safe-error', [$rpo, OtpravkaApi::PRINT_FILE]),
        caseDef('otpravka', 'returns', 'returnShipment', 'safe-error', [$rpo, 'UNDEFINED']),
        caseDef('otpravka', 'returns', 'createReturnShipment', $includeLiveMutating ? 'mutating' : 'safe-error', [$returnPayload]),
        caseDef('otpravka', 'returns', 'editReturnShipment', 'safe-error', [sampleReturnShipmentEntity(), $rpo]),
        caseDef('otpravka', 'returns', 'deleteReturnShipment', 'safe-error', [$rpo]),
        caseDef('otpravka', 'post-offices', 'searchPostOfficeByIndex', 'read', ['410012']),
        caseDef('otpravka', 'post-offices', 'searchPostOfficeByAddress', 'read', ['Саратов, Московская, 109', 3]),
        caseDef('otpravka', 'post-offices', 'searchPostOfficeByCoordinates', 'read', [['latitude' => '51.533557', 'longitude' => '46.034257', 'top' => 3]]),
        caseDef('otpravka', 'post-offices', 'getPostOfficeServices', 'read', ['410012']),
        caseDef('otpravka', 'post-offices', 'getPostalCodesInLocality', 'read', ['Саратов', 'Саратовская область']),
        caseDef('calculation', 'tariffs', 'getCategoryList', 'read', []),
        caseDef('calculation', 'tariffs', 'getCategoryDescription', 'read', [27030]),
        caseDef('calculation', 'tariffs', 'getTariff', 'read', [27030, sampleTariffPayload(), []]),
        caseDef('calculation', 'tariffs', 'getTariffAndDeliveryPeriod', 'read', [27030, sampleTariffPayload(), []]),
        caseDef('calculation', 'tariffs', 'getObjectInfo', 'read', [27030]),
        caseDef('calculation', 'tariffs', 'getCountryList', 'read', []),
        caseDef('tracking', 'tracking', 'getOperationsByRpo', 'tracking-read', [$rpo, 'RUS']),
        caseDef('tracking', 'tracking', 'getNpayInfo', 'tracking-read', [$rpo, 'RUS']),
        caseDef('tracking', 'tracking', 'getTickets', 'tracking-mutating', [[$rpo], 'RUS']),
        caseDef('tracking', 'tracking', 'getOperationsByTicket', 'tracking-read', [$ticket]),
    ];
}

function caseDef(string $target, string $group, string $method, string $sideEffects, array $arguments): array
{
    return [
        'target' => $target,
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
        'target' => $case['target'],
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
        $target = resolveTarget($client, $case['target']);
        $snapshot['result'] = redactAccountSnapshot(
            $case['target'],
            $case['method'],
            redact($target->{$case['method']}(...$case['arguments']))
        );
    } catch (Throwable $exception) {
        $snapshot['status'] = 'error';
        $snapshot['error'] = exceptionToArray($exception);
    } finally {
        $snapshot['duration_ms'] = (int) round((microtime(true) - $startedAt) * 1000);
    }

    return $snapshot;
}

function resolveTarget(Otpravkapochtaru $client, string $target): object
{
    return match ($target) {
        'facade' => $client,
        'otpravka' => $client->otpravkaApi(),
        'calculation' => $client->calculation(),
        'tracking' => $client->trackingApi(),
        default => throw new InvalidArgumentException('Unknown snapshot target: ' . $target),
    };
}

function sampleTariffPayload(): array
{
    return [
        'from' => 410012,
        'to' => 455001,
        'weight' => 1000,
    ];
}

function sampleRecipientEntity(): Recipient
{
    $recipient = new Recipient();
    $recipient->setAddress('455001, Челябинская область, Магнитогорск, Ленина, 1');
    $recipient->setName('Иванов Иван');
    $recipient->setPhone('79000000000');

    return $recipient;
}

function sampleOrderEntity(): Order
{
    $order = new Order();
    $order->setOrderNum('docs-' . gmdate('Ymd-His'));
    $order->setIndexTo('455001');
    $order->setRegionTo('Челябинская область');
    $order->setPlaceTo('Магнитогорск');
    $order->setStreetTo('Ленина');
    $order->setHouseTo('1');
    $order->setRecipientName('Иванов Иван');
    $order->setTelAddress('79000000000');
    $order->setMailType('POSTAL_PARCEL');
    $order->setMailCategory('ORDINARY');
    $order->setMass(1000);

    return $order;
}

function sampleReturnShipmentEntity(): ReturnShipment
{
    $from = new AddressReturn();
    $from->setIndex('410012');
    $from->setPlace('Саратов');
    $from->setStreet('Московская');
    $from->setHouse('109');

    $shipment = new ReturnShipment();
    $shipment->setAddressFrom($from);
    $shipment->setMailType('UNDEFINED');
    $shipment->setRecipientName('Иванов Иван');
    $shipment->setSenderName('ООО Ромашка');
    $shipment->setPostofficeCode('410012');

    return $shipment;
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
    if ($value instanceof UploadedFileInterface) {
        return [
            'client_filename' => $value->getClientFilename(),
            'client_media_type' => $value->getClientMediaType(),
            'size' => $value->getSize(),
            'error' => $value->getError(),
        ];
    }

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
        $value = preg_replace('/(AccessToken|Authorization|X-User-Authorization|Password|Login|Email|Phone|Inn|Hid|AgreementNumber)(\s*[:=]\s*)[^\s,;]+/i', '$1$2[redacted]', $value) ?? $value;
        $value = preg_replace('/Bearer\s+[A-Za-z0-9._~+\/-]+=*/i', 'Bearer [redacted]', $value) ?? $value;
    }

    return $value;
}

function redactAccountSnapshot(string $target, string $method, mixed $value): mixed
{
    if ($target !== 'facade' && $target !== 'otpravka') {
        return $value;
    }

    if (!in_array($method, ['getAccountInfo', 'settings'], true) || !is_array($value)) {
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
