# Real REST Shipping API Assurance - 2026-07-11

## Scope

- Joomla package: `WT Otpravkapochtaru` 3.0.0.
- Runtime: installed package on `http://joomla.local/`.
- Route: `410000 Саратов` -> `685000 Магадан`.
- Tracking excluded by request and unavailable credentials.
- API limit protection: local hard cap 40, actual use 29; `getApiLimit()` was not called.

## Execution

A single controlled HTTP run invoked the installed Joomla library. Each REST call was counted before execution and captured separately. Raw captures remain in `.webtolk/tmp/rest-api-capture-20260711/raw/`; they may contain account data and are git-ignored.

## Results

- Total REST calls: 29.
- Successful: 25.
- API/HTTP errors: 4.
- Skipped after execution began: 0.
- Tracking calls: 0.
- API-limit balance calls: 0.

Successful coverage includes normalization, account/settings alias, shipping points, recipient reliability, tariff/delivery period alias, post-office searches, post-office services, locality postal codes and the full order lifecycle.

The order lifecycle passed end to end:

`createOrders -> editOrder -> findOrderById -> findOrderByShopId -> createBatch -> getOrdersInBatch -> findOrderByRpo -> returnOrdersToNew -> deleteOrders`

Cleanup state confirms `order_returned_to_new=true` and `order_deleted=true`.

## Error Evidence

- `generateDocumentPackage`: HTTP 400 for the newly created batch in status `CREATED`.
- `generateDocumentF103`: HTTP 400; observed batch has `electronic-f103=false`.
- `createReturnShipment`: JSON business error `DIRECT_SHIPMENT_NOT_FOUND`.
- `createReturnShipments`: JSON business error `FREE_ER_ADDRESS_NOT_ENABLED`.

Separate-return edit/delete were not invoked because creation failed and no safe created identifier existed. Direct-return deletion was not invoked because direct-return creation failed. No cleanup entity from either return path remained.

## Public Documentation Artifacts

- `docs/api-schemas/otpravka/index.json`: 29 observed contracts.
- `docs/api-schemas/otpravka/examples/`: 27 anonymized real JSON response examples.
- `docs/api-schemas/otpravka/schemas/`: 27 JSON Schema Draft 2020-12 files.
- `docs/api-schemas/otpravka/README.md`: scope, results, errors, anonymization and limitations.

Two HTTP 400 document calls have no public example/schema because the library throws before exposing a decoded response body. Their exception metadata is recorded in the public index; no response shape was fabricated.

## Privacy And Packaging Gates

- Private account fields were redacted and source-value comparison found 0 leaks.
- 55 public JSON files parse successfully.
- 27 examples conform structurally to their generated schemas.
- Public scan found 0 email addresses and 0 Windows paths; phone/barcode-like regex hits were localized to SHA-256 values.
- Raw captures and verifier scripts are ignored by `.gitignore` through `.webtolk/`.
- Release ZIP contains 48 entries and 0 `docs/api-schemas/` entries.

## Tooling

- PHPStorm MCP: file creation/patching, inspections, integrated-terminal execution, project tree and glob checks.
- Serena MCP: targeted scenario-code searches before execution.
- No ordinary shell fallback was needed for the live-test slice.

## Residual Risks

- JSON Schemas represent one observed response per operation, not the complete upstream contract.
- Document response bodies remain unknown for the observed HTTP 400 path.
- Return creation/edit/delete require an account where the relevant return service is enabled and a shipment eligible for direct return.
