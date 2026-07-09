<?php

/**
 * @package       WT Otpravkapochtaru
 * @version     3.0.0
 * @author     Sergey Tolkachyov
 * @copyright  Copyright (c) 2026 Sergey Tolkachyov, WebTolk. All rights reserved.
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

    private Request $request;

    private TrackingEntity $tracking;

    public function __construct(?CredentialsProvider $credentialsProvider = null)
    {
        $credentialsProvider ??= new CredentialsProvider();

        $this->request  = new Request($credentialsProvider);
        $this->tracking = new TrackingEntity(new SoapRequest($credentialsProvider));
    }

    public function getAccountInfo(): array
    {
        return $this->request->get('/1.0/settings', [], Request::ENDPOINT_OTPRAVKA);
    }

    public function getShippingPoints(): array
    {
        return $this->request->get('/1.0/user-shipping-points', [], Request::ENDPOINT_OTPRAVKA);
    }

    public function getApiLimit(): array
    {
        return $this->request->get('/1.0/settings/limit', [], Request::ENDPOINT_OTPRAVKA);
    }

    public function getSettings(): array
    {
        return $this->request->get('/1.0/settings', [], Request::ENDPOINT_OTPRAVKA);
    }

    /**
     * @param list<Order|array<string, mixed>> $orders
     */
    public function createOrders(array $orders): array
    {
        return $this->request->putJson('/1.0/user/backlog', $this->normalizeOrderPayloads($orders), Request::ENDPOINT_OTPRAVKA);
    }

    public function editOrder(Order|array $order, int|string $id): array
    {
        return $this->request->putJson('/1.0/backlog/' . $id, $this->normalizeOrderPayload($order), Request::ENDPOINT_OTPRAVKA);
    }

    public function findOrderById(int|string $id): array
    {
        return $this->request->get('/1.0/backlog/' . $id, [], Request::ENDPOINT_OTPRAVKA);
    }

    public function findOrderByShopId(string $orderNumber): array
    {
        return $this->request->get('/1.0/backlog/search', ['query' => $orderNumber], Request::ENDPOINT_OTPRAVKA);
    }

    public function findOrderByRpo(string $rpo): array
    {
        return $this->request->get('/1.0/shipment/search', ['query' => $rpo], Request::ENDPOINT_OTPRAVKA);
    }

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
     * @param list<Recipient|array<string, mixed>> $recipients
     */
    public function getRecipientsReliability(array $recipients): array
    {
        return $this->request->postJson(
            '/1.0/unreliable-recipient',
            $this->normalizeRecipientPayloads($recipients),
            Request::ENDPOINT_OTPRAVKA
        );
    }

    public function deleteOrders(array $orderIds): array
    {
        return $this->request->deleteJson('/1.0/backlog', $orderIds, Request::ENDPOINT_OTPRAVKA);
    }

    public function returnOrdersToNew(array $orderIds): array
    {
        return $this->request->postJson('/1.0/user/backlog', $orderIds, Request::ENDPOINT_OTPRAVKA);
    }

    public function createBatch(array $orderIds, ?string $sendingDate = null, bool $useOnlineBalance = false): array
    {
        $query = [];

        if ($sendingDate !== null && $sendingDate !== '') {
            $query['sending-date']       = $sendingDate;
            $query['use-online-balance'] = $useOnlineBalance ? 'true' : 'false';
        }

        return $this->request->postJson('/1.0/user/shipment', $orderIds, Request::ENDPOINT_OTPRAVKA, $query);
    }

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

    public function generateDocumentF103(string $batchName): array
    {
        return $this->request->getBinary('/1.0/forms/' . $batchName . '/f103pdf', [], Request::ENDPOINT_OTPRAVKA);
    }

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
     * @param list<ReturnShipment|array<string, mixed>> $returnShipments
     */
    public function createReturnShipments(array $returnShipments): array
    {
        return $this->request->putJson(
            '/1.0/returns/return-without-direct',
            $this->normalizeReturnShipmentPayloads($returnShipments),
            Request::ENDPOINT_OTPRAVKA
        );
    }

    public function editReturnShipment(ReturnShipment|array $returnShipment, string $rpo): array
    {
        return $this->request->postJson(
            '/1.0/returns/' . $rpo,
            $this->normalizeReturnShipmentPayload($returnShipment),
            Request::ENDPOINT_OTPRAVKA
        );
    }

    public function deleteReturnShipment(string $rpo): array
    {
        return $this->request->delete(
            '/1.0/returns/delete-separate-return',
            ['barcode' => $rpo],
            Request::ENDPOINT_OTPRAVKA
        );
    }

    public function getTariff(int|string $objectId, array $params, array $services = []): array
    {
        return $this->request->postJson(
            '/1.0/tariff',
            $this->buildTariffParams($objectId, $params, $services),
            Request::ENDPOINT_OTPRAVKA,
        );
    }

    public function getTariffAndDeliveryPeriod(int|string $objectId, array $params, array $services = []): array
    {
        return $this->request->postJson(
            '/1.0/tariff',
            $this->buildTariffParams($objectId, $params, $services),
            Request::ENDPOINT_OTPRAVKA,
        );
    }

    public function getCountryList(): array
    {
        return CountryDictionary::all();
    }

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

    public function searchPostOfficeByCoordinates(array $params): array
    {
        $params['filter'] ??= 'ALL';

        return $this->request->get('/1.0/nearby', $params, Request::ENDPOINT_POSTOFFICE);
    }

    public function getPostOfficeServices(int|string $postalCode, ?string $serviceGroup = null): array
    {
        $path = '/1.0/' . $postalCode . '/services';

        if ($serviceGroup !== null && $serviceGroup !== '') {
            $path .= '/' . $serviceGroup;
        }

        return $this->request->get($path, [], Request::ENDPOINT_POSTOFFICE);
    }

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

    public function getOperationsByRpo(string $rpo, string $lang = self::TRACKING_DEFAULT_LANG): array
    {
        return $this->tracking->getOperationsByRpo($rpo, $lang);
    }

    public function getNpayInfo(string $rpo, string $lang = self::TRACKING_DEFAULT_LANG): array
    {
        return $this->tracking->getNpayInfo($rpo, $lang);
    }

    public function getTickets(array $rpoList, string $lang = self::TRACKING_DEFAULT_LANG): array
    {
        return $this->tracking->getTickets($rpoList, $lang);
    }

    public function getOperationsByTicket(string $ticket): array
    {
        return $this->tracking->getOperationsByTicket($ticket);
    }

    /**
     * @param array<string, scalar|null> $params
     * @param list<string|int> $services
     *
     * @return array<string, scalar|null>
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
     * @param list<Order|array<string, mixed>> $orders
     *
     * @return list<array<string, mixed>>
     */
    private function normalizeOrderPayloads(array $orders): array
    {
        return array_map($this->normalizeOrderPayload(...), $orders);
    }

    /**
     * @return array<string, mixed>
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
     * @param list<Recipient|array<string, mixed>> $recipients
     *
     * @return list<array<string, mixed>>
     */
    private function normalizeRecipientPayloads(array $recipients): array
    {
        return array_map($this->normalizeRecipientPayload(...), $recipients);
    }

    /**
     * @return array<string, mixed>
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
     * @param list<ReturnShipment|array<string, mixed>> $returnShipments
     *
     * @return list<array<string, mixed>>
     */
    private function normalizeReturnShipmentPayloads(array $returnShipments): array
    {
        return array_map($this->normalizeReturnShipmentPayload(...), $returnShipments);
    }

    /**
     * @return array<string, mixed>
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
