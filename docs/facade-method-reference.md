# Facade Method Reference

The stable entry point is `Webtolk\Otpravkapochtaru\Otpravkapochtaru`.

## Account And Settings

- `getAccountInfo(): array`
- `getShippingPoints(): array`
- `getApiLimit(): array`
- `getSettings(): array`

## Orders And Recipients

- `createOrders(array $orders): array`
- `editOrder(int|string $id, array|object $order): array`
- `findOrderById(int|string $id): array`
- `findOrderByShopId(int|string $shopId): array`
- `findOrderByRpo(string $rpo): array`
- `getRecipientReliability(array|object $recipient): array`
- `getRecipientsReliability(array $recipients): array`
- `deleteOrders(array $ids): array`
- `returnOrdersToNew(array $ids): array`

## Batches And Documents

- `createBatch(string $name, array $orderIds): array`
- `getAllBatches(array $params = []): array`
- `getOrdersInBatch(string $batchName): array`
- `generateDocumentPackage(string $batchName, string $printType = 'paper'): array`
- `generateDocumentF103(string $batchName, string $printType = 'one-sided'): array`

## Tariffs And Post Offices

- `getTariff(int|string $objectId, array $params, array $services = []): array`
- `getTariffAndDeliveryPeriod(int|string $objectId, array $params, array $services = []): array`
- `getCountryList(): array`
- `searchPostOfficeByIndex(int|string $postalCode, ?string $latitude = null, ?string $longitude = null, ?string $currentDateTime = null, bool $filterByOfficeType = true, bool $ufpsPostalCode = false): array`
- `searchPostOfficeByAddress(string $address, ?string $top = null): array`
- `searchPostOfficeByCoordinates(string $latitude, string $longitude, ?string $top = null): array`
- `getPostOfficeServices(int|string $postalCode): array`
- `getPostalCodesInLocality(string $region, string $place, ?string $area = null, ?string $street = null): array`

## Returns And Tracking

- `createReturnShipment(array|object $shipment): array`
- `createReturnShipments(array $shipments): array`
- `editReturnShipment(int|string $id, array|object $shipment): array`
- `deleteReturnShipment(int|string $id): array`
- `getOperationsByRpo(string $rpo, string $lang = 'RUS'): array`
- `getNpayInfo(string $rpo, string $lang = 'RUS'): array`
- `getTickets(array $rpoList, string $lang = 'RUS'): array`
- `getOperationsByTicket(string $ticket, string $lang = 'RUS'): array`

## More Detail

- [Developer API](developer-api.md)
- [Thin wrapper architecture](thin-wrapper-architecture.md)
- [Low-level API status](low-level-api.md)
