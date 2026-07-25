<?php

/**
 * Public facade for Russian Post shipment, post office and tracking operations.
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

use Webtolk\Otpravkapochtaru\Configuration\CredentialsProvider;
use Webtolk\Otpravkapochtaru\Dictionaries\CountryDictionary;
use Webtolk\Otpravkapochtaru\Entity\Order;
use Webtolk\Otpravkapochtaru\Entity\Recipient;
use Webtolk\Otpravkapochtaru\Entity\ReturnShipment;
use Webtolk\Otpravkapochtaru\Exception\ValidationException;

final class Otpravkapochtaru
{
    private const TRACKING_DEFAULT_LANG = 'RUS';

    /**
     * REST request helper for Otpravka and post office endpoints.
     *
     * @var    Request
     * @since  3.0.0
     */
    private Request $request;

    /**
     * SOAP tracking helper.
     *
     * @var    TrackingEntity
     * @since  3.0.0
     */
    private TrackingEntity $tracking;

    /**
     * Build the REST and SOAP helpers from explicit credentials or from the Joomla plugin parameters.
     *
     * @since 3.0.0
     */
    public function __construct(?CredentialsProvider $credentialsProvider = null)
    {
        $credentialsProvider ??= new CredentialsProvider();

        $this->request  = new Request($credentialsProvider);
        $this->tracking = new TrackingEntity(new SoapRequest($credentialsProvider));
    }

    /**
     * Load account settings for the configured Russian Post Otpravka API user.
     *
     * @since 3.0.0
     */
    public function getAccountInfo(): array
    {
        return $this->request->get('/1.0/settings', [], Request::ENDPOINT_OTPRAVKA);
    }

    /**
     * Return shipment points available to the configured Otpravka API account.
     *
     * @since 3.0.0
     */
    public function getShippingPoints(): array
    {
        return $this->request->get('/1.0/user-shipping-points', [], Request::ENDPOINT_OTPRAVKA);
    }

    /**
     * Load current API request limit information for the configured account.
     *
     * @since 3.0.0
     */
    public function getApiLimit(): array
    {
        return $this->request->get('/1.0/settings/limit', [], Request::ENDPOINT_OTPRAVKA);
    }

    /**
     * Load raw account settings from the Otpravka API settings endpoint.
     *
     * @since 3.0.0
     */
    public function getSettings(): array
    {
        return $this->request->get('/1.0/settings', [], Request::ENDPOINT_OTPRAVKA);
    }

    /**
     * Create one or more draft orders in the Russian Post backlog.
     *
     * Entity objects and plain arrays are normalized to the API payload contract before sending.
     *
     * @param list<Order|array<string, mixed>> $orders
     *
     * @since 3.0.0
     */
    public function createOrders(array $orders): array
    {
        return $this->request->putJson('/1.0/user/backlog', $this->normalizeOrderPayloads($orders), Request::ENDPOINT_OTPRAVKA);
    }

    /**
     * Replace an existing backlog order payload by its Russian Post order identifier.
     *
     * @since 3.0.0
     */
    public function editOrder(Order|array $order, int|string $id): array
    {
        return $this->request->putJson('/1.0/backlog/' . $id, $this->normalizeOrderPayload($order), Request::ENDPOINT_OTPRAVKA);
    }

    /**
     * Find a backlog order by the Russian Post internal order identifier.
     *
     * @since 3.0.0
     */
    public function findOrderById(int|string $id): array
    {
        return $this->request->get('/1.0/backlog/' . $id, [], Request::ENDPOINT_OTPRAVKA);
    }

    /**
     * Search backlog orders by the shop order number stored in the order payload.
     *
     * @since 3.0.0
     */
    public function findOrderByShopId(string $orderNumber): array
    {
        return $this->request->get('/1.0/backlog/search', ['query' => $orderNumber], Request::ENDPOINT_OTPRAVKA);
    }

    /**
     * Search shipped orders by RPO barcode.
     *
     * @since 3.0.0
     */
    public function findOrderByRpo(string $rpo): array
    {
        return $this->request->get('/1.0/shipment/search', ['query' => $rpo], Request::ENDPOINT_OTPRAVKA);
    }

    /**
     * Check a single recipient against the Russian Post unreliable-recipient service.
     *
     * The API endpoint accepts a list, so the method wraps one recipient and unwraps the first response item.
     *
     * @since 3.0.0
     */
    public function getRecipientReliability(Recipient|array $recipient): array
    {
        $result = $this->request->postJson(
            '/1.0/unreliable-recipient',
            [$this->normalizeRecipientPayload($recipient)],
            Request::ENDPOINT_OTPRAVKA
        );

        return isset($result[0]) && is_array($result[0]) ? $result[0] : $result;
    }

    /**
     * Check several recipients against the Russian Post unreliable-recipient service.
     *
     * @param list<Recipient|array<string, mixed>> $recipients
     *
     * @since 3.0.0
     */
    public function getRecipientsReliability(array $recipients): array
    {
        return $this->request->postJson(
            '/1.0/unreliable-recipient',
            $this->normalizeRecipientPayloads($recipients),
            Request::ENDPOINT_OTPRAVKA
        );
    }

    /**
     * Delete draft orders from the backlog by Russian Post order identifiers.
     *
     * @since 3.0.0
     */
    public function deleteOrders(array $orderIds): array
    {
        return $this->request->deleteJson('/1.0/backlog', $orderIds, Request::ENDPOINT_OTPRAVKA);
    }

    /**
     * Return previously prepared orders back to the "new" backlog state.
     *
     * @since 3.0.0
     */
    public function returnOrdersToNew(array $orderIds): array
    {
        return $this->request->postJson('/1.0/user/backlog', $orderIds, Request::ENDPOINT_OTPRAVKA);
    }

    /**
     * Create a shipment batch from order identifiers.
     *
     * Optional sending date enables the API query flags for planned sending date and online balance usage.
     *
     * @since 3.0.0
     */
    public function createBatch(array $orderIds, ?string $sendingDate = null, bool $useOnlineBalance = false): array
    {
        $query = [];

        if ($sendingDate !== null && $sendingDate !== '') {
            $query['sending-date']       = $sendingDate;
            $query['use-online-balance'] = $useOnlineBalance ? 'true' : 'false';
        }

        return $this->request->postJson('/1.0/user/shipment', $orderIds, Request::ENDPOINT_OTPRAVKA, $query);
    }

    /**
     * List created shipment batches with optional mail type, category and pagination filters.
     *
     * @since 3.0.0
     */
    public function getAllBatches(?string $mailType = null, ?string $mailCategory = null, ?int $size = null, string $sort = 'ask', ?int $page = null): array
    {
        $query = ['sort' => $sort];

        if ($mailType !== null && $mailType !== '') {
            $query['mailType'] = $mailType;
        }

        if ($mailCategory !== null && $mailCategory !== '') {
            $query['mailCategory'] = $mailCategory;
        }

        if ($size !== null) {
            $query['size'] = $size;
        }

        if ($page !== null) {
            $query['page'] = $page;
        }

        return $this->request->get('/1.0/batch', $query, Request::ENDPOINT_OTPRAVKA);
    }

    /**
     * List orders included in a named shipment batch.
     *
     * @since 3.0.0
     */
    public function getOrdersInBatch(string $batchName, ?int $size = null, string $sort = 'ask', ?int $page = null): array
    {
        $query = ['sort' => $sort];

        if ($size !== null) {
            $query['size'] = $size;
        }

        if ($page !== null) {
            $query['page'] = $page;
        }

        return $this->request->get('/1.0/batch/' . $batchName . '/shipment', $query, Request::ENDPOINT_OTPRAVKA);
    }

    /**
     * Generate and download the complete ZIP document package for a shipment batch.
     *
     * @since 3.0.0
     */
    public function generateDocumentPackage(string $batchName, string $printType = 'paper', string $printTypeForm = 'one-sided'): array
    {
        return $this->request->getBinary(
            '/1.0/forms/' . $batchName . '/zip-all',
            [
                'print-type'      => $printType,
                'print-type-form' => $printTypeForm,
            ],
            Request::ENDPOINT_OTPRAVKA,
        );
    }

    /**
     * Generate and download the F103 PDF form for a shipment batch.
     *
     * @since 3.0.0
     */
    public function generateDocumentF103(string $batchName): array
    {
        return $this->request->getBinary('/1.0/forms/' . $batchName . '/f103pdf', [], Request::ENDPOINT_OTPRAVKA);
    }

    /**
     * Create a return shipment for an existing direct barcode.
     *
     * @since 3.0.0
     */
    public function createReturnShipment(string $directBarcode, string $mailType = 'UNDEFINED'): array
    {
        return $this->request->putJson(
            '/1.0/returns',
            [
                'direct-barcode' => $directBarcode,
                'mail-type'      => $mailType,
            ],
            Request::ENDPOINT_OTPRAVKA
        );
    }

    /**
     * Create return shipments without a direct source shipment barcode.
     *
     * @param list<ReturnShipment|array<string, mixed>> $returnShipments
     *
     * @since 3.0.0
     */
    public function createReturnShipments(array $returnShipments): array
    {
        return $this->request->putJson(
            '/1.0/returns/return-without-direct',
            $this->normalizeReturnShipmentPayloads($returnShipments),
            Request::ENDPOINT_OTPRAVKA
        );
    }

    /**
     * Update a separate return shipment identified by its RPO barcode.
     *
     * @since 3.0.0
     */
    public function editReturnShipment(ReturnShipment|array $returnShipment, string $rpo): array
    {
        return $this->request->postJson(
            '/1.0/returns/' . $rpo,
            $this->normalizeReturnShipmentPayload($returnShipment),
            Request::ENDPOINT_OTPRAVKA
        );
    }

    /**
     * Delete a separate return shipment by RPO barcode.
     *
     * @since 3.0.0
     */
    public function deleteReturnShipment(string $rpo): array
    {
        return $this->request->delete(
            '/1.0/returns/delete-separate-return',
            ['barcode' => $rpo],
            Request::ENDPOINT_OTPRAVKA
        );
    }

    /**
     * Calculate tariff for a mail object and optional additional service codes.
     *
     * @since 3.0.0
     */
    public function getTariff(int|string $objectId, array $params, array $services = []): array
    {
        return $this->request->postJson(
            '/1.0/tariff',
            $this->buildTariffParams($objectId, $params, $services),
            Request::ENDPOINT_OTPRAVKA,
        );
    }

    /**
     * Calculate tariff and delivery period for a mail object.
     *
     * The current Russian Post endpoint returns both values from the same `/1.0/tariff` API call.
     *
     * @since 3.0.0
     */
    public function getTariffAndDeliveryPeriod(int|string $objectId, array $params, array $services = []): array
    {
        return $this->request->postJson(
            '/1.0/tariff',
            $this->buildTariffParams($objectId, $params, $services),
            Request::ENDPOINT_OTPRAVKA,
        );
    }

    /**
     * Return the packaged country dictionary used by Russian Post payloads.
     *
     * @since 3.0.0
     */
    public function getCountryList(): array
    {
        return CountryDictionary::all();
    }

    /**
     * Find a post office by postal code and optional coordinates/date filters.
     *
     * @since 3.0.0
     */
    public function searchPostOfficeByIndex(
        int|string $postalCode,
        ?string $latitude = null,
        ?string $longitude = null,
        ?string $currentDateTime = null,
        bool $filterByOfficeType = true,
        bool $ufpsPostalCode = false,
    ): array {
        $query = [
            'latitude'              => $latitude,
            'longitude'             => $longitude,
            'filter-by-office-type' => $filterByOfficeType,
            'ufps-postal-code'      => $ufpsPostalCode,
        ];

        if ($currentDateTime !== null && $currentDateTime !== '') {
            $query['current-date-time'] = $currentDateTime;
        }

        return $this->request->get('/1.0/' . $postalCode, $query, Request::ENDPOINT_POSTOFFICE);
    }

    /**
     * Search nearby post offices by free-form address.
     *
     * @since 3.0.0
     */
    public function searchPostOfficeByAddress(string $address, int $count = 3): array
    {
        return $this->request->get(
            '/1.0/by-address',
            [
                'address' => $address,
                'top'     => $count,
            ],
            Request::ENDPOINT_POSTOFFICE,
        );
    }

    /**
     * Search nearby post offices by coordinate/filter parameters.
     *
     * If no filter is provided the method asks the API for all office types.
     *
     * @since 3.0.0
     */
    public function searchPostOfficeByCoordinates(array $params): array
    {
        $params['filter'] ??= 'ALL';

        return $this->request->get('/1.0/nearby', $params, Request::ENDPOINT_POSTOFFICE);
    }

    /**
     * Load services available in a post office, optionally narrowed to a service group.
     *
     * @since 3.0.0
     */
    public function getPostOfficeServices(int|string $postalCode, ?string $serviceGroup = null): array
    {
        $path = '/1.0/' . $postalCode . '/services';

        if ($serviceGroup !== null && $serviceGroup !== '') {
            $path .= '/' . $serviceGroup;
        }

        return $this->request->get($path, [], Request::ENDPOINT_POSTOFFICE);
    }

    /**
     * Return postal codes for a locality and optional region/district filters.
     *
     * @since 3.0.0
     */
    public function getPostalCodesInLocality(string $locality, string $region = '', string $district = ''): array
    {
        return $this->request->get(
            '/1.0/settlement.offices.codes',
            [
                'settlement' => $locality,
                'region'     => $region,
                'district'   => $district,
            ],
            Request::ENDPOINT_POSTOFFICE,
        );
    }

    /**
     * Load tracking operation history for one RPO barcode through the single-access SOAP service.
     *
     * @since 3.0.0
     */
    public function getOperationsByRpo(string $rpo, string $lang = self::TRACKING_DEFAULT_LANG): array
    {
        return $this->tracking->getOperationsByRpo($rpo, $lang);
    }

    /**
     * Load cash-on-delivery postal order events for one RPO barcode through SOAP.
     *
     * @since 3.0.0
     */
    public function getNpayInfo(string $rpo, string $lang = self::TRACKING_DEFAULT_LANG): array
    {
        return $this->tracking->getNpayInfo($rpo, $lang);
    }

    /**
     * Create batch tracking tickets for a list of RPO barcodes.
     *
     * @since 3.0.0
     */
    public function getTickets(array $rpoList, string $lang = self::TRACKING_DEFAULT_LANG): array
    {
        return $this->tracking->getTickets($rpoList, $lang);
    }

    /**
     * Load batch tracking results by a previously created tracking ticket.
     *
     * @since 3.0.0
     */
    public function getOperationsByTicket(string $ticket): array
    {
        return $this->tracking->getOperationsByTicket($ticket);
    }

    /**
     * Merge tariff object and additional service codes into the flat API payload.
     *
     * @param array<string, scalar|null> $params
     * @param list<string|int> $services
     *
     * @return array<string, scalar|null>
     *
     * @since 3.0.0
     */
    private function buildTariffParams(int|string $objectId, array $params, array $services): array
    {
        $params['object'] = (string) $objectId;

        if ($services !== []) {
            $params['service'] = implode(',', array_map('strval', $services));
        }

        return $params;
    }

    /**
     * Normalize a list of order entities or arrays into Russian Post order payload arrays.
     *
     * @param list<Order|array<string, mixed>> $orders
     *
     * @return list<array<string, mixed>>
     *
     * @since 3.0.0
     */
    private function normalizeOrderPayloads(array $orders): array
    {
        return array_map($this->normalizeOrderPayload(...), $orders);
    }

    /**
     * Convert one order entity or array into a validated Russian Post order payload.
     *
     * @return array<string, mixed>
     *
     * @since 3.0.0
     */
    private function normalizeOrderPayload(Order|array $order): array
    {
        if ($order instanceof Order) {
            return $order->toArray();
        }

        if (!is_array($order)) {
            throw new ValidationException('Order payload must be an array or Order entity.');
        }

        return Order::fromArray($order)->toArray();
    }

    /**
     * Normalize a list of recipient entities or arrays into unreliable-recipient payload arrays.
     *
     * @param list<Recipient|array<string, mixed>> $recipients
     *
     * @return list<array<string, mixed>>
     *
     * @since 3.0.0
     */
    private function normalizeRecipientPayloads(array $recipients): array
    {
        return array_map($this->normalizeRecipientPayload(...), $recipients);
    }

    /**
     * Convert one recipient entity or array into a validated unreliable-recipient payload.
     *
     * @return array<string, mixed>
     *
     * @since 3.0.0
     */
    private function normalizeRecipientPayload(Recipient|array $recipient): array
    {
        if ($recipient instanceof Recipient) {
            return $recipient->toArray();
        }

        if (!is_array($recipient)) {
            throw new ValidationException('Recipient payload must be an array or Recipient entity.');
        }

        return Recipient::fromArray($recipient)->toArray();
    }

    /**
     * Normalize a list of return shipment entities or arrays into API payload arrays.
     *
     * @param list<ReturnShipment|array<string, mixed>> $returnShipments
     *
     * @return list<array<string, mixed>>
     *
     * @since 3.0.0
     */
    private function normalizeReturnShipmentPayloads(array $returnShipments): array
    {
        return array_map($this->normalizeReturnShipmentPayload(...), $returnShipments);
    }

    /**
     * Convert one return shipment entity or array into a validated API payload.
     *
     * @return array<string, mixed>
     *
     * @since 3.0.0
     */
    private function normalizeReturnShipmentPayload(ReturnShipment|array $returnShipment): array
    {
        if ($returnShipment instanceof ReturnShipment) {
            return $returnShipment->toArray();
        }

        if (!is_array($returnShipment)) {
            throw new ValidationException('Return shipment payload must be an array or ReturnShipment entity.');
        }

        return ReturnShipment::fromArray($returnShipment)->toArray();
    }
}
