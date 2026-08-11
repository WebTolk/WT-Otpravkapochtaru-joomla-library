<?php

/**
 * Joomla facade for Russian Post operations built on top of the upstream SDK.
 *
 * @package     WT Otpravkapochtaru
 * @version     3.0.0
 * @author      Sergey Tolkachyov
 * @copyright   Copyright (c) 2026 Sergey Tolkachyov, WebTolk. All rights reserved.
 * @license     GNU/GPL 3.0
 * @since       0.1.0
 */

namespace Webtolk\Otpravkapochtaru;

defined('_JEXEC') or die;

require_once __DIR__ . '/libraries/vendor/autoload.php';

use LapayGroup\RussianPost\Http\Psr18Transport;
use LapayGroup\RussianPost\Providers\Calculation;
use LapayGroup\RussianPost\Entity\Order as LapayOrder;
use LapayGroup\RussianPost\Entity\Recipient as LapayRecipient;
use LapayGroup\RussianPost\Entity\AddressReturn;
use LapayGroup\RussianPost\Entity\Item;
use LapayGroup\RussianPost\Entity\CustomsDeclaration;
use LapayGroup\RussianPost\Entity\CustomsDeclarationItem;
use LapayGroup\RussianPost\Entity\ReturnShipment as LapayReturnShipment;
use LapayGroup\RussianPost\Entity\EcomData;
use LapayGroup\RussianPost\Providers\OtpravkaApi;
use LapayGroup\RussianPost\Providers\Tracking;
use Psr\Http\Message\UploadedFileInterface;
use Webtolk\Otpravkapochtaru\Joomla\Psr18TransportFactory;
use Webtolk\Otpravkapochtaru\Joomla\CredentialsProvider;
use Webtolk\Otpravkapochtaru\Joomla\UploadedFileSerializer;

final class Otpravkapochtaru
{
    private const TRACKING_DEFAULT_LANG = 'RUS';

    private const TRACKING_SERVICE_SINGLE = 'single';

    /**
     * REST tracking configuration keys.
     *
     * @var array<string, mixed>
     */
    private const PRINT_TYPE_MAP = [
        'paper' => OtpravkaApi::PRINT_TYPE_PAPER,
        'thermo' => OtpravkaApi::PRINT_TYPE_THERMO,
        'two-sided' => OtpravkaApi::PRINT_TWO_SIDED,
        'two_sided' => OtpravkaApi::PRINT_TWO_SIDED,
        'twosided' => OtpravkaApi::PRINT_TWO_SIDED,
        'one-sided' => OtpravkaApi::PRINT_ONE_SIDED,
        'one_sided' => OtpravkaApi::PRINT_ONE_SIDED,
        'onesided' => OtpravkaApi::PRINT_ONE_SIDED,
    ];

    private const PRINT_TYPE_FORM_MAP = [
        'one-sided' => OtpravkaApi::PRINT_ONE_SIDED,
        'onesided' => OtpravkaApi::PRINT_ONE_SIDED,
        'one_sided' => OtpravkaApi::PRINT_ONE_SIDED,
        'paper' => OtpravkaApi::PRINT_ONE_SIDED,
        'two-sided' => OtpravkaApi::PRINT_TWO_SIDED,
        'twosided' => OtpravkaApi::PRINT_TWO_SIDED,
        'two_sided' => OtpravkaApi::PRINT_TWO_SIDED,
    ];

    private CredentialsProvider $credentialsProvider;
    private Psr18Transport $transport;
    private OtpravkaApi $otpravkaApi;
    private Calculation $calculation;
    private ?Tracking $trackingApi = null;

    /**
     * Build upstream SDK clients for REST and tariff APIs.
     */
    public function __construct(array|object|null $credentialsProvider = null)
    {
        $this->credentialsProvider = $credentialsProvider ?? new CredentialsProvider();

        $this->transport = Psr18TransportFactory::create(
            $this->credentialsProvider->getHttpTimeout()
        );

        $this->otpravkaApi = new OtpravkaApi($this->resolveOtpravkaConfig(), $this->transport);
        $this->calculation = new Calculation($this->transport);
    }

    /**
     * Load account settings from settings endpoint.
     */
    public function getAccountInfo(): array
    {
        return $this->otpravkaApi->settings();
    }

    /**
     * Load shipment points configured for account.
     */
    public function getShippingPoints(): array
    {
        return $this->otpravkaApi->shippingPoints();
    }

    /**
     * Load API request limits for account.
     */
    public function getApiLimit(): array
    {
        return $this->getLegacyEndpoint('settings/limit');
    }

    /**
     * Load raw settings from upstream SDK endpoint.
     */
    public function getSettings(): array
    {
        return $this->otpravkaApi->settings();
    }

    /**
     * Create backlog orders.
     *
     * @param list<array<string, mixed>|object> $orders
     */
    public function createOrders(array $orders): array
    {
        return $this->otpravkaApi->createOrders($this->normalizeUpstreamOrders($orders));
    }

    /**
     * Replace backlog order by internal identifier.
     */
    public function editOrder(array|object $order, int|string $id): array
    {
        return $this->otpravkaApi->editOrder($this->normalizeUpstreamOrder($order), (string) $id);
    }

    /**
     * Find backlog order by internal order id.
     */
    public function findOrderById(int|string $id): array
    {
        return $this->otpravkaApi->findOrderById((string) $id);
    }

    /**
     * Find backlog order by shop id.
     */
    public function findOrderByShopId(string $orderNumber): array
    {
        return $this->otpravkaApi->findOrderByShopId($orderNumber);
    }

    /**
     * Find shipment by RPO.
     */
    public function findOrderByRpo(string $rpo): array
    {
        return $this->otpravkaApi->findOrderByRpo($rpo);
    }

    /**
     * Verify one recipient against reliability endpoint.
     */
    public function getRecipientReliability(array|object $recipient): array
    {
        $result = $this->otpravkaApi->untrustworthyRecipient($this->normalizeRecipient($recipient));

        if (!is_array($result) || $result === []) {
            return [];
        }

        return is_array($result[0] ?? null) ? $result[0] : $result;
    }

    /**
     * Verify multiple recipients against reliability endpoint.
     *
     * @param list<array<string, mixed>|object> $recipients
     */
    public function getRecipientsReliability(array $recipients): array
    {
        return $this->otpravkaApi->untrustworthyRecipients($this->normalizeRecipients($recipients));
    }

    /**
     * Delete backlog orders by ids.
     */
    public function deleteOrders(array $orderIds): array
    {
        return $this->otpravkaApi->deleteOrders($orderIds);
    }

    /**
     * Return backlog orders to "new" state.
     */
    public function returnOrdersToNew(array $orderIds): array
    {
        return $this->otpravkaApi->returnToNew($orderIds);
    }

    /**
     * Create shipment batch.
     */
    public function createBatch(array $orderIds, ?string $sendingDate = null, bool $useOnlineBalance = false): array
    {
        $date = $this->normalizeDate($sendingDate);

        return $this->otpravkaApi->createBatch($orderIds, $date, $useOnlineBalance);
    }

    /**
     * List batches with optional filters.
     */
    public function getAllBatches(?string $mailType = null, ?string $mailCategory = null, ?int $size = null, string $sort = 'ask', ?int $page = null): array
    {
        return $this->otpravkaApi->getAllBatches($mailType, $mailCategory, $size, $sort, $page);
    }

    /**
     * List orders in a batch.
     */
    public function getOrdersInBatch(string $batchName, ?int $size = null, string $sort = 'ask', ?int $page = null): array
    {
        return $this->otpravkaApi->getOrdersInBatch($batchName, $size, $sort, $page);
    }

    /**
     * Generate batch document package.
     */
    public function generateDocumentPackage(string $batchName, string $printType = 'paper', string $printTypeForm = 'one-sided'): array
    {
        $result = $this->otpravkaApi->generateDocPackage(
            $batchName,
            OtpravkaApi::PRINT_FILE,
            $this->normalizePrintType($printType),
            $this->normalizePrintTypeForm($printTypeForm),
        );

        return $this->normalizeUploadedFile($result);
    }

    /**
     * Generate F103 PDF form for batch.
     */
    public function generateDocumentF103(string $batchName): array
    {
        return $this->normalizeUploadedFile($this->otpravkaApi->generateDocF103($batchName, OtpravkaApi::PRINT_FILE));
    }

    /**
     * Create a separate return shipment.
     */
    public function createReturnShipment(string $directBarcode, string $mailType = 'UNDEFINED'): array
    {
        return $this->otpravkaApi->returnShipment($directBarcode, $mailType);
    }

    /**
     * Create return shipments by payload arrays.
     *
     * @param list<array<string, mixed>|object> $returnShipments
     */
    public function createReturnShipments(array $returnShipments): array
    {
        return $this->otpravkaApi->createReturnShipment($this->normalizeReturnShipments($returnShipments));
    }

    /**
     * Edit return shipment by RPO.
     */
    public function editReturnShipment(array|object $returnShipment, string $rpo): array
    {
        return $this->otpravkaApi->editReturnShipment($this->normalizeReturnShipment($returnShipment), $rpo);
    }

    /**
     * Delete return shipment by RPO.
     */
    public function deleteReturnShipment(string $rpo): array
    {
        return $this->otpravkaApi->deleteReturnShipment($rpo);
    }

    /**
     * Calculate tariff by object and services.
     */
    public function getTariff(int|string $objectId, array $params, array $services = []): array
    {
        return $this->calculation->getTariff((int) $objectId, $this->normalizeTariffParams($objectId, $params), $services);
    }

    /**
     * Calculate tariff and period by object and services.
     */
    public function getTariffAndDeliveryPeriod(int|string $objectId, array $params, array $services = []): array
    {
        return $this->calculation->getTariffAndDeliveryPeriod((int) $objectId, $this->normalizeTariffParams($objectId, $params), $services);
    }

    /**
     * Get country list from tariff service.
     */
    public function getCountryList(): array
    {
        return $this->calculation->getCountryList();
    }

    /**
     * Search post offices by index.
     */
    public function searchPostOfficeByIndex(
        int|string $postalCode,
        ?string $latitude = null,
        ?string $longitude = null,
        ?string $currentDateTime = null,
        bool $filterByOfficeType = true,
        bool $ufpsPostalCode = false,
    ): array {
        return $this->otpravkaApi->searchPostOfficeByIndex($postalCode, $latitude, $longitude, $currentDateTime, $filterByOfficeType, $ufpsPostalCode);
    }

    /**
     * Search post offices by address.
     */
    public function searchPostOfficeByAddress(string $address, int $count = 3): array
    {
        return $this->otpravkaApi->searchPostOfficeByAddress($address, $count);
    }

    /**
     * Search nearby post offices.
     */
    public function searchPostOfficeByCoordinates(array $params): array
    {
        $params['filter'] ??= 'ALL';

        return $this->otpravkaApi->searchPostOfficeByCoordinates($params);
    }

    /**
     * Get post office service list.
     */
    public function getPostOfficeServices(int|string $postalCode, ?string $serviceGroup = null): array
    {
        return $this->otpravkaApi->getPostOfficeServices($postalCode, $serviceGroup);
    }

    /**
     * Get postal codes in locality by region / district.
     */
    public function getPostalCodesInLocality(string $locality, string $region = '', string $district = ''): array
    {
        return $this->otpravkaApi->getPostalCodesInLocality($locality, $region, $district);
    }

    /**
     * Resolve shipment history with SOAP tracker (single tracking endpoint).
     */
    public function getOperationsByRpo(string $rpo, string $lang = self::TRACKING_DEFAULT_LANG): array
    {
        return $this->trackingApi()->getOperationsByRpo($rpo, $lang);
    }

    /**
     * Resolve COD events with SOAP tracker.
     */
    public function getNpayInfo(string $rpo, string $lang = self::TRACKING_DEFAULT_LANG): array
    {
        return $this->trackingApi()->getNpayInfo($rpo, $lang);
    }

    /**
     * Create batch tracking ticket.
     */
    public function getTickets(array $rpoList, string $lang = self::TRACKING_DEFAULT_LANG): array
    {
        return $this->trackingApi()->getTickets($rpoList, $lang);
    }

    /**
     * Load tracking operations by ticket.
     */
    public function getOperationsByTicket(string $ticket): array
    {
        return $this->trackingApi()->getOperationsByTicket($ticket);
    }

    /**
     * Merge object and additional services for tariff endpoint.
     *
     * @param array<string, scalar|null> $params
     */
    private function normalizeTariffParams(int|string $objectId, array $params): array
    {
        $params['object'] = (string) $objectId;

        return $params;
    }

    /**
     * Normalize order payloads for LapayGroup SDK `Order` entities.
     *
     * @param list<array<string, mixed>|LapayOrder> $orders
     *
     * @return list<LapayOrder>
     */
    private function normalizeUpstreamOrders(array $orders): array
    {
        return array_map($this->normalizeUpstreamOrder(...), $orders);
    }

    /**
     * Normalize one order payload for LapayGroup SDK `Order` entity.
     */
    private function normalizeUpstreamOrder(array|object $order): LapayOrder
    {
        if ($order instanceof LapayOrder) {
            return $order;
        }

        if (!is_array($order)) {
            throw new \InvalidArgumentException(
                'Order payload must be an array or LapayGroup\\RussianPost\\Entity\\Order instance.'
            );
        }

        $orderEntity = new LapayOrder();
        $orderPayload = $this->normalizePayloadKeys($order);

        if (isset($orderPayload['goods'])) {
            if (!is_array($orderPayload['goods'])) {
                throw new \InvalidArgumentException(
                    'Order payload field \"goods\" must be an array for LapayGroup\\RussianPost\\Entity\\Order.'
                );
            }

            $this->hydrateLapayOrderItems($orderEntity, $orderPayload['goods']);
            if (isset($orderPayload['goods']['dimension']) && is_array($orderPayload['goods']['dimension'])) {
                $this->hydrateLapayOrderDimension(
                    $orderEntity,
                    $this->normalizePayloadKeys($orderPayload['goods']['dimension'])
                );
            }

            unset($orderPayload['goods']);
        }

        if (isset($orderPayload['dimension'])) {
            if (is_array($orderPayload['dimension'])) {
                $this->hydrateLapayOrderDimension(
                    $orderEntity,
                    $this->normalizePayloadKeys($orderPayload['dimension'])
                );
            }

            unset($orderPayload['dimension']);
        }

        $this->hydrateLapayEntityFromPayload(
            $orderEntity,
            $orderPayload,
            [
                'postoffice_code' => 'setPostOfficeCode',
                'postofficecode'  => 'setPostOfficeCode',
            ]
        );

        return $orderEntity;
    }

    /**
     * Normalize recipient collection payload for LapayGroup SDK `Recipient` entities.
     *
     * @param list<array<string, mixed>|LapayRecipient> $recipients
     *
     * @return list<LapayRecipient>
     */
    private function normalizeRecipients(array $recipients): array
    {
        return array_map($this->normalizeRecipient(...), $recipients);
    }

    /**
     * Normalize one recipient payload for LapayGroup SDK `Recipient` entity.
     */
    private function normalizeRecipient(array|object $recipient): LapayRecipient
    {
        if ($recipient instanceof LapayRecipient) {
            return $recipient;
        }

        if (!is_array($recipient)) {
            throw new \InvalidArgumentException(
                'Recipient payload must be an array or LapayGroup\\RussianPost\\Entity\\Recipient instance.'
            );
        }

        $recipientEntity = new LapayRecipient();
        $this->hydrateLapayEntityFromPayload(
            $recipientEntity,
            $this->normalizePayloadKeys($recipient),
            [
                'raw_full_name'  => 'setName',
                'raw-full-name'  => 'setName',
                'raw_address'    => 'setAddress',
                'raw-address'    => 'setAddress',
                'raw_telephone'  => 'setPhone',
                'raw-telephone'  => 'setPhone',
            ]
        );

        return $recipientEntity;
    }

    /**
     * Normalize one return-shipment payload for LapayGroup SDK `ReturnShipment` entity.
     */
    private function normalizeReturnShipment(array|object $returnShipment): LapayReturnShipment
    {
        if ($returnShipment instanceof LapayReturnShipment) {
            return $returnShipment;
        }

        if (!is_array($returnShipment)) {
            throw new \InvalidArgumentException(
                'Return shipment payload must be an array or LapayGroup\\RussianPost\\Entity\\ReturnShipment instance.'
            );
        }

        $returnShipmentEntity = new LapayReturnShipment();
        $this->hydrateLapayEntityFromPayload(
            $returnShipmentEntity,
            $this->normalizePayloadKeys($returnShipment),
            [
                'postoffice_code' => 'setPostOfficeCode',
                'postofficecode'  => 'setPostOfficeCode',
                'address_from'    => 'setAddressFrom',
                'address-from'    => 'setAddressFrom',
                'address_to'      => 'setAddressTo',
                'address-to'      => 'setAddressTo',
            ]
        );

        return $returnShipmentEntity;
    }

    /**
     * Hydrate a LapayGroup entity using known setters; skip unknown keys to keep wrapper tolerant.
     *
     * @param array<string, mixed> $payload
     */
    private function hydrateLapayEntityFromPayload(object $entity, array $payload, array $setterMap = []): void
    {
        foreach ($payload as $rawKey => $value) {
            $normalizedKey = $this->normalizePayloadKey((string) $rawKey);
            if ($normalizedKey === '') {
                continue;
            }

            $setter = $setterMap[$normalizedKey] ?? $this->resolveSetterName($normalizedKey);

            if (!method_exists($entity, $setter)) {
                continue;
            }

            $entity->{$setter}(
                $this->normalizeValueForLapayEntitySetter(
                    $entity,
                    $setter,
                    $value
                )
            );
        }
    }

    /**
     * Normalize payload key as snake_case.
     *
     * @param string $key
     */
    private function normalizePayloadKey(string $key): string
    {
        $key = strtolower(trim($key));
        $key = str_replace('-', '_', $key);
        $key = (string) preg_replace('/[^a-z0-9_]+/', '_', $key);
        $key = preg_replace('/_+/', '_', (string) $key);

        return trim((string) $key, '_');
    }

    /**
     * Resolve a setter from snake_case key.
     */
    private function resolveSetterName(string $normalizedKey): string
    {
        $parts = preg_split('/_+/', $normalizedKey);
        if ($parts === false) {
            return '';
        }

        $setter = '';
        foreach ($parts as $part) {
            if ($part === '') {
                continue;
            }
            $setter .= ucfirst($part);
        }

        $setter = 'set' . $setter;
        return str_replace('Postoffice', 'PostOffice', $setter);
    }

    /**
     * Normalize array keys for object payloads while preserving item lists.
     *
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     */
    private function normalizePayloadKeys(array $payload): array
    {
        if (array_is_list($payload)) {
            return $payload;
        }

        $normalized = [];
        foreach ($payload as $key => $value) {
            if (!is_string($key)) {
                continue;
            }
            $normalized[$this->normalizePayloadKey($key)] = $value;
        }

        return $normalized;
    }

    /**
     * Build nested values for special setters.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    private function normalizeValueForLapayEntitySetter(object $entity, string $setter, mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        if ($entity instanceof LapayOrder) {
            if ($setter === 'setItems') {
                return $this->buildLapayItems($value);
            }

            if ($setter === 'setCustomsDeclaration') {
                return $this->buildLapayCustomsDeclaration($value);
            }

            if ($setter === 'setEcomData') {
                return $this->buildLapayEcomData($value);
            }
        }

        if ($entity instanceof LapayReturnShipment && in_array($setter, ['setAddressFrom', 'setAddressTo'], true)) {
            return $this->buildLapayAddressReturn($value);
        }

        return $value;
    }

    /**
     * Normalize return shipment and order address payload with upstream `AddressReturn` entity.
     *
     * @param mixed $payload
     */
    private function buildLapayAddressReturn(mixed $payload): ?AddressReturn
    {
        if ($payload === null) {
            return null;
        }

        if ($payload instanceof AddressReturn) {
            return $payload;
        }

        if (!is_array($payload)) {
            throw new \InvalidArgumentException('Address payload must be an array or AddressReturn instance.');
        }

        $address = new AddressReturn();
        $this->hydrateLapayEntityFromPayload($address, $this->normalizePayloadKeys($payload));

        return $address;
    }

    /**
     * Hydrate order goods payload.
     *
     * @param array<string, mixed> $goods
     */
    private function hydrateLapayOrderItems(LapayOrder $order, array $goods): void
    {
        if (array_is_list($goods)) {
            $order->setItems($this->buildLapayItems($goods));

            return;
        }

        if (!array_key_exists('items', $goods) || !is_array($goods['items'])) {
            return;
        }

        $order->setItems($this->buildLapayItems($goods['items']));
    }

    /**
     * Hydrate order dimensions (`height`, `length`, `width`, `dimension-type`).
     *
     * @param array<string, mixed> $dimension
     */
    private function hydrateLapayOrderDimension(LapayOrder $order, array $dimension): void
    {
        $dimension = $this->normalizePayloadKeys($dimension);

        if (array_key_exists('height', $dimension)) {
            $height = $this->normalizeNumericValue($dimension['height']);
            $order->setHeight($height);
        }

        if (array_key_exists('length', $dimension)) {
            $length = $this->normalizeNumericValue($dimension['length']);
            $order->setLength($length);
        }

        if (array_key_exists('width', $dimension)) {
            $width = $this->normalizeNumericValue($dimension['width']);
            $order->setWidth($width);
        }

        if (array_key_exists('dimension_type', $dimension)) {
            $order->setDimensionType($dimension['dimension_type']);
        }
    }

    /**
     * Build LapayGroup items.
     *
     * @param mixed $payload
     *
     * @return list<Item>
     */
    private function buildLapayItems(mixed $payload): array
    {
        if (!is_array($payload)) {
            throw new \InvalidArgumentException('Order items payload must be an array.');
        }

        if ($payload === []) {
            return [];
        }

        $itemsPayload = $payload;
        if (!array_is_list($payload) && !isset($payload[0])) {
            $itemsPayload = [$payload];
        }

        $items = [];
        foreach ($itemsPayload as $itemPayload) {
            if ($itemPayload instanceof Item) {
                $items[] = $itemPayload;
                continue;
            }

            if (!is_array($itemPayload)) {
                throw new \InvalidArgumentException('Order item must be an array or LapayGroup\\RussianPost\\Entity\\Item instance.');
            }

            $item = new Item();
            $this->hydrateLapayEntityFromPayload($item, $this->normalizePayloadKeys($itemPayload));
            $items[] = $item;
        }

        return $items;
    }

    /**
     * Build LapayGroup customs declaration.
     *
     * @param mixed $payload
     */
    private function buildLapayCustomsDeclaration(mixed $payload): ?CustomsDeclaration
    {
        if ($payload === null) {
            return null;
        }

        if ($payload instanceof CustomsDeclaration) {
            return $payload;
        }

        if (!is_array($payload)) {
            throw new \InvalidArgumentException('Order field \"customs-declaration\" must be an array or CustomsDeclaration instance.');
        }

        if (array_key_exists('customs_entries', $payload) && is_array($payload['customs_entries'])) {
            $payload['customs_entries'] = $this->buildLapayCustomsDeclarationItems($payload['customs_entries']);
        } elseif (array_key_exists('customs-entries', $payload) && is_array($payload['customs-entries'])) {
            $payload['customs_entries'] = $this->buildLapayCustomsDeclarationItems($payload['customs-entries']);
            unset($payload['customs-entries']);
        }

        $customsDeclaration = new CustomsDeclaration();
        $this->hydrateLapayEntityFromPayload($customsDeclaration, $this->normalizePayloadKeys($payload));

        return $customsDeclaration;
    }

    /**
     * Build LapayGroup customs declaration items.
     *
     * @param mixed $payload
     *
     * @return list<CustomsDeclarationItem>
     */
    private function buildLapayCustomsDeclarationItems(mixed $payload): array
    {
        if (!is_array($payload)) {
            throw new \InvalidArgumentException('Customs declaration entries payload must be an array.');
        }

        if ($payload === []) {
            return [];
        }

        $entriesPayload = $payload;
        if (!array_is_list($payload) && !isset($payload[0])) {
            $entriesPayload = [$payload];
        }

        $entries = [];
        foreach ($entriesPayload as $entryPayload) {
            if ($entryPayload instanceof CustomsDeclarationItem) {
                $entries[] = $entryPayload;
                continue;
            }

            if (!is_array($entryPayload)) {
                throw new \InvalidArgumentException('Customs declaration entry must be an array or CustomsDeclarationItem instance.');
            }

            $entry = new CustomsDeclarationItem();
            $this->hydrateLapayEntityFromPayload($entry, $this->normalizePayloadKeys($entryPayload));
            $entries[] = $entry;
        }

        return $entries;
    }

    /**
     * Build LapayGroup ecom data.
     *
     * @param mixed $payload
     */
    private function buildLapayEcomData(mixed $payload): ?EcomData
    {
        if ($payload === null) {
            return null;
        }

        if ($payload instanceof EcomData) {
            return $payload;
        }

        if (!is_array($payload)) {
            throw new \InvalidArgumentException('Order field \"ecom-data\" must be an array or EcomData instance.');
        }

        $ecomData = new EcomData();
        $this->hydrateLapayEntityFromPayload($ecomData, $this->normalizePayloadKeys($payload));

        return $ecomData;
    }

    /**
     * Normalize numeric scalar payload values.
     */
    private function normalizeNumericValue(mixed $value): mixed
    {
        if ($value === '' || $value === null) {
            return null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        return $value;
    }

    /**
     * Normalize return shipment collection payload for LapayGroup SDK `ReturnShipment` entities.
     *
     * @param list<array<string, mixed>|LapayReturnShipment> $returnShipments
     *
     * @return list<LapayReturnShipment>
     */
    private function normalizeReturnShipments(array $returnShipments): array
    {
        return array_map($this->normalizeReturnShipment(...), $returnShipments);
    }

    /**
     * Resolve SOAP tracking API lazily and keep REST-only flows unblocked.
     */
    private function trackingApi(): Tracking
    {
        if ($this->trackingApi instanceof Tracking) {
            return $this->trackingApi;
        }

        if ($this->credentialsProvider->getTrackingLogin() === '' || $this->credentialsProvider->getTrackingPassword() === '') {
            throw new \RuntimeException('Tracking credentials are not configured. Fill tracking_login and tracking_password to use SOAP tracking methods.');
        }

        $trackingConfig = $this->resolveTrackingConfig();

        try {
            $this->trackingApi = new Tracking(
                self::TRACKING_SERVICE_SINGLE,
                $trackingConfig,
                max(1, $this->credentialsProvider->getHttpTimeout())
            );
        } catch (\Throwable $exception) {
            throw new \RuntimeException(
                'Tracking service is unavailable. Ensure SOAP extension and tracking credentials are configured.',
                0,
                $exception
            );
        }

        return $this->trackingApi;
    }

    /**
     * Build upstream otpravka auth config with backwards-compatible keys.
     *
     * @return array<string, mixed>
     */
    private function resolveOtpravkaConfig(): array
    {
        $authConfig = [
            'token' => $this->credentialsProvider->getAccessToken(),
            'key'   => $this->credentialsProvider->getUserAuthorizationHeader(),
        ];

        return ['auth' => ['otpravka' => $authConfig]];
    }

    /**
     * Build upstream tracking auth config.
     *
     * @return array<string, mixed>
     */
    private function resolveTrackingConfig(): array
    {
        return [
            'auth' => [
                'tracking' => [
                    'login'    => $this->credentialsProvider->getTrackingLogin(),
                    'password' => $this->credentialsProvider->getTrackingPassword(),
                ],
            ],
        ];
    }

    /**
     * Build auth headers for legacy REST endpoints not covered by SDK methods.
     */
    private function restHeaders(): array
    {
        return [
            'Authorization'         => 'AccessToken ' . $this->credentialsProvider->getAccessToken(),
            'X-User-Authorization' => 'Basic ' . $this->credentialsProvider->getUserAuthorizationHeader(),
            'Content-Type'         => 'application/json',
            'Accept'               => 'application/json;charset=UTF-8',
        ];
    }

    /**
     * Fallback call for unsupported endpoints.
     *
     * @throws \RuntimeException
     */
    private function getLegacyEndpoint(string $path): array
    {
        $response = $this->transport->send(
            'GET',
            'https://otpravka-api.pochta.ru/1.0/' . ltrim($path, '/'),
            $this->restHeaders(),
        );

        $body = $response->getBody();
        if ($body->isSeekable()) {
            $body->rewind();
        }

        $rawBody = (string) $body->getContents();
        $statusCode = $response->getStatusCode();

        if ($statusCode >= 400) {
            throw new \RuntimeException(sprintf('Legacy REST GET /1.0/%s failed with status %d.', $path, $statusCode), $statusCode);
        }

        if (trim($rawBody) === '') {
            throw new \RuntimeException(sprintf('Legacy REST GET /1.0/%s returned empty response body.', $path), $statusCode);
        }

        try {
            $decoded = json_decode($rawBody, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new \RuntimeException(sprintf('Legacy REST GET /1.0/%s returned invalid JSON.', $path), $statusCode, $exception);
        }

        if (!is_array($decoded)) {
            throw new \RuntimeException(sprintf('Legacy REST GET /1.0/%s returned non-array payload.', $path), $statusCode);
        }

        return $decoded;
    }

    /**
     * Normalize uploaded file responses to legacy array format.
     *
     * @return array{
     *     content: string,
     *     contentType: string,
     *     fileName: string|null,
     *     statusCode: int,
     *     headers: array<string, mixed>
     * }
     *
     * @throws \RuntimeException
     */
    private function normalizeUploadedFile(mixed $result): array
    {
        if ($result instanceof UploadedFileInterface) {
            return UploadedFileSerializer::toArray($result);
        }

        if (is_array($result)) {
            return $result;
        }

        throw new \RuntimeException('Russian Post document endpoint returned unexpected response type.');
    }

    /**
     * Normalize printable type.
     */
    private function normalizePrintType(string $value): string
    {
        $normalized = strtolower(trim($value));
        return self::PRINT_TYPE_MAP[$normalized] ?? OtpravkaApi::PRINT_TYPE_PAPER;
    }

    /**
     * Normalize printable form type.
     */
    private function normalizePrintTypeForm(string $value): string
    {
        $normalized = strtolower(trim($value));
        return self::PRINT_TYPE_FORM_MAP[$normalized] ?? OtpravkaApi::PRINT_ONE_SIDED;
    }

    /**
     * Normalize send date from string to DateTimeImmutable.
     */
    private function normalizeDate(?string $date): ?\DateTimeImmutable
    {
        if ($date === null || $date === '') {
            return null;
        }

        try {
            return new \DateTimeImmutable($date);
        } catch (\Throwable $exception) {
            throw new \InvalidArgumentException(sprintf('Invalid sending date "%s".', $date), 0, $exception);
        }
    }

}
