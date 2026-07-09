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

use Webtolk\Otpravkapochtaru\Exception\TrackingException;

final class TrackingEntity
{
    private const NAMESPACE_DATA = 'http://russianpost.org/operationhistory/data';
    private const NPAY_NAMESPACE = 'http://www.russianpost.org/RTM/DataExchangeESPP/Data';

    public function __construct(private readonly SoapRequest $soapRequest)
    {
    }

    public function getOperationsByRpo(string $rpo, string $lang = 'RUS'): array
    {
        try {
            $response = $this->singleClient()->getOperationHistory($this->buildHistoryPayload($rpo, $lang));
        } catch (\Throwable $exception) {
            throw new TrackingException('Failed to request tracking operation history.', (int) $exception->getCode(), $exception);
        }

        return $this->normalizeCollection($response->OperationHistoryData->historyRecord ?? []);
    }

    public function getNpayInfo(string $rpo, string $lang = 'RUS'): array
    {
        try {
            $response = $this->singleClient()->PostalOrderEventsForMail($this->buildNpayPayload($rpo, $lang));
        } catch (\Throwable $exception) {
            throw new TrackingException('Failed to request postal order events.', (int) $exception->getCode(), $exception);
        }

        return $this->normalizeCollection($response->PostalOrderEventsForMaiOutput->PostalOrderEvent ?? []);
    }

    /**
     * @param list<string> $rpoList
     *
     * @return array{tickets: list<string>, not_create: list<string>}
     */
    public function getTickets(array $rpoList, string $lang = 'RUS'): array
    {
        $result = ['tickets' => [], 'not_create' => []];

        foreach (array_chunk($rpoList, 500) as $chunk) {
            $requestPayload                = new \stdClass();
            $requestPayload->login         = $this->soapRequest->getTrackingLogin();
            $requestPayload->password      = $this->soapRequest->getTrackingPassword();
            $requestPayload->language      = $lang;
            $requestPayload->request       = new \stdClass();
            $requestPayload->request->Item = [];

            foreach ($chunk as $rpo) {
                $item                            = new \stdClass();
                $item->Barcode                   = $rpo;
                $requestPayload->request->Item[] = $item;
            }

            try {
                $response = $this->packClient()->getTicket($requestPayload);
            } catch (\Throwable $exception) {
                throw new TrackingException('Failed to request batch tracking ticket.', (int) $exception->getCode(), $exception);
            }

            if (!empty($response->value) && is_string($response->value)) {
                $result['tickets'][] = $response->value;

                continue;
            }

            $result['not_create'] = array_merge($result['not_create'], $chunk);
        }

        return $result;
    }

    public function getOperationsByTicket(string $ticket): array
    {
        $requestPayload           = new \stdClass();
        $requestPayload->login    = $this->soapRequest->getTrackingLogin();
        $requestPayload->password = $this->soapRequest->getTrackingPassword();
        $requestPayload->ticket   = $ticket;

        try {
            $response = $this->packClient()->getResponseByTicket($requestPayload);
        } catch (\Throwable $exception) {
            throw new TrackingException('Failed to request tracking results by ticket.', (int) $exception->getCode(), $exception);
        }

        if (empty($response->value) || empty($response->value->Item)) {
            return [];
        }

        return $this->normalizeCollection($response->value->Item);
    }

    private function singleClient(): \SoapClient
    {
        return $this->soapRequest->createSingleClient();
    }

    private function packClient(): \SoapClient
    {
        return $this->soapRequest->createPackClient();
    }

    private function buildHistoryPayload(string $rpo, string $lang): \SoapVar
    {
        return new \SoapVar(
            [
                new \SoapVar(
                    [
                        new \SoapVar($rpo, XSD_STRING, null, null, 'Barcode', self::NAMESPACE_DATA),
                        new \SoapVar(0, XSD_INT, null, null, 'MessageType', self::NAMESPACE_DATA),
                        new \SoapVar($lang, XSD_STRING, null, null, 'Language', self::NAMESPACE_DATA),
                    ],
                    SOAP_ENC_OBJECT,
                    null,
                    null,
                    'OperationHistoryRequest',
                    self::NAMESPACE_DATA
                ),
                new \SoapVar(
                    [
                        new \SoapVar($this->soapRequest->getTrackingLogin(), XSD_STRING, null, null, 'login', self::NAMESPACE_DATA),
                        new \SoapVar($this->soapRequest->getTrackingPassword(), XSD_STRING, null, null, 'password', self::NAMESPACE_DATA),
                    ],
                    SOAP_ENC_OBJECT,
                    null,
                    null,
                    'AuthorizationHeader',
                    self::NAMESPACE_DATA
                ),
            ],
            SOAP_ENC_OBJECT
        );
    }

    private function buildNpayPayload(string $rpo, string $lang): \SoapVar
    {
        return new \SoapVar(
            [
                new \SoapVar(
                    [
                        new \SoapVar($this->soapRequest->getTrackingLogin(), XSD_STRING, null, null, 'login', self::NAMESPACE_DATA),
                        new \SoapVar($this->soapRequest->getTrackingPassword(), XSD_STRING, null, null, 'password', self::NAMESPACE_DATA),
                    ],
                    SOAP_ENC_OBJECT,
                    null,
                    null,
                    'AuthorizationHeader',
                    self::NAMESPACE_DATA
                ),
                new \SoapVar(
                    '<ns2:PostalOrderEventsForMailInput Barcode="' . htmlspecialchars($rpo, ENT_QUOTES) . '" Language="' . htmlspecialchars($lang, ENT_QUOTES) . '" />',
                    XSD_ANYXML,
                    null,
                    null,
                    'PostalOrderEventsForMailInput',
                    self::NPAY_NAMESPACE
                ),
            ],
            SOAP_ENC_OBJECT
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function normalizeCollection(mixed $value): array
    {
        if ($value === null || $value === []) {
            return [];
        }

        if (!is_array($value)) {
            $value = [$value];
        }

        return array_map(
            fn (mixed $item): array => (array) $this->normalizeNode($item),
            $value
        );
    }

    private function normalizeNode(mixed $value): mixed
    {
        if (is_array($value)) {
            $normalized = [];

            foreach ($value as $key => $item) {
                $normalized[$key] = $this->normalizeNode($item);
            }

            return $normalized;
        }

        if (is_object($value)) {
            return $this->normalizeNode(get_object_vars($value));
        }

        return $value;
    }
}
