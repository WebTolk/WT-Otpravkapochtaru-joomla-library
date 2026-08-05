# Order Tracking Runtime Assurance 2026-07-25

## Scope

- Create a fresh test order through the installed `joomla.local` library API.
- Use the barcode/RPO from that order to verify SOAP tracking credentials and tracking methods.
- Keep raw runtime evidence under ignored `.webtolk/tmp/`.

## Platform Context

- Project platform: Joomla extension package.
- Runtime target: `D:/OSPanel/home/joomla.local/public`.
- Library source used by runtime: installed Joomla library under `JPATH_LIBRARIES/Webtolk/Otpravkapochtaru`.
- Credentials source: `system/wt_otpravkapochtaru` plugin parameters.
- Joomla checks applied: installed package runtime, plugin parameter loading, SOAP extension availability, tracking SOAP calls.

## Execution

- Script: `.webtolk/tmp/verify/joomla-local-create-order-and-tracking-20260725.php`.
- Dump root: `.webtolk/tmp/order-tracking-check-20260725/`.
- Order number: `codex-order-tracking-20260725_183153`.
- Created order id: `2333724273`.
- Created barcode/RPO: `80214523462306`.
- Tracking credentials: login present, password present; secret values were not written to this report.

## Results

- Environment: ok; PHP SOAP extension loaded.
- Address/FIO/phone normalization: ok.
- `getShippingPoints`: ok.
- `createOrders`: ok; API returned result id `2333724273`.
- `findOrderByShopId`: ok; returned the created order and barcode `80214523462306`.
- `findOrderByRpo`: ok transport/API call, empty result for the newly created order.
- `getOperationsByRpo`: ok; returned 1 tracking record.
- `getNpayInfo`: ok; returned an empty collection, expected for a non-COD test parcel.
- `getTickets`: ok transport/API call; returned no tickets and `not_create` contained `80214523462306`.
- `getOperationsByTicket`: skipped because no ticket was returned.

## Tracking Evidence

The single SOAP tracking call returned an operation history record for barcode `80214523462306`:

- operation type: `Присвоение идентификатора`
- destination index: `455039`
- operation address: `АО "Почта России"`
- mail type: `Посылка`
- mail category: `Обыкновенное`
- recipient: `Иванов Иван Иванович`

## Residuals

- The created test order remains in the Russian Post backlog for later inspection unless explicitly cleaned up.
- Batch ticket flow did not produce a ticket for this fresh barcode; this is recorded as a partial coverage boundary for `getOperationsByTicket`, not as a failed credential check.
- Raw dumps contain test recipient/order data and must stay local under `.webtolk/tmp/`.
