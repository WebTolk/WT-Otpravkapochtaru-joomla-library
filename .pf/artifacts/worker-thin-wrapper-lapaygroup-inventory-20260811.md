# LapayGroup SDK Inventory

Run: `lapaygroup-thin-wrapper-migration-20260811`
Task: `t19-lapaygroup-sdk-inventory`
Source: `.pf/tmp/LapayGroup-RussianPost-2.0.0/RussianPost-2.0.0`

## SDK metadata summary
| Item | Value |
|---|---|
| Package | `lapaygroup/russianpost` |
| Version | source directory `2.0.0` |
| PHP requirement | `^8.3` |
| HTTP transport core | `psr/http-client`, `psr/http-factory`, `psr/http-message` |
| Logging | `psr/log` |
| Required PHP extensions | `ext-soap` (tracking via SOAP), `ext-mbstring` |
| Autoload | `LapayGroup\\RussianPost\\` => `src/` |
| Test/dev | `phpunit`, `phpstan` in require-dev |

## Transport implementations and requirements
| Component | Type | Purpose | Required inputs |
|---|---|---|---|
| `LapayGroup\RussianPost\Http\Psr18Transport` | HTTP transport class | Sends PSR-18 requests and builds uploaded file objects | `ClientInterface`, `RequestFactoryInterface`, `StreamFactoryInterface`, `UploadedFileFactoryInterface` |
| `LapayGroup\RussianPost\Providers\Calculation` | API provider | Calls `https://delivery.pochta.ru/v2/...` for tariff/dictionary data | `Psr18Transport` + JSON endpoints + GET/POST handling |
| `LapayGroup\RussianPost\Providers\OtpravkaApi` | API provider | Calls `https://otpravka-api.pochta.ru/` and `/postoffice/` and parses JSON/File responses | `Psr18Transport`, `AccessToken` + `Basic` headers, optional file response parsing |
| `LapayGroup\RussianPost\Providers\Tracking` | API provider (legacy SOAP) | Tracking history/tickets through `https://tracking.pochta.ru` WSDLs | `ext-soap`, SOAP client factory, `config['auth']['tracking']['login|password']`, login/password auth |

## Providers table
| Provider class | Public operations | Data/feature coverage |
|---|---|---|
| `Calculation` | `getCategoryList`, `getCategoryDescription`, `getTariff`, `getTariffAndDeliveryPeriod`, `getObjectInfo`, `getCountryList` | тариф, сроки доставки, справочники, расчёты |
| `OtpravkaApi` | `getDeliveryTariff`, `clearAddress`, `clearFio`, `clearPhone`, `shippingPoints`, `settings`, `getBalance`, `untrustworthyRecipient`, `untrustworthyRecipients`, `createOrders`, `createOrdersV2`, `editOrder`, `findOrderById`, `findOrderByShopId`, `findOrderByRpo`, `findOrderInBatch`, `deleteOrders`, `returnToNew`, `createBatch`, `getAllBatches`, `moveOrdersToBatch`, `findBatchByName`, `addOrdersToBatch`, `deleteOrdersInBatch`, `getOrdersInBatch`, `changeBatchSendingDay`, `getArchivedBatches`, `archivingBatch`, `unarchivingBatch`, `generateDocPackage`, `generateDocF7p`, `generateDocF112ek`, `generateDocOrderPrintForm`, `generateDocF103`, `generateDocCheckingAct`, `sendingF103form`, `generateReturnLabel`, `searchPostOfficeByIndex`, `searchPostOfficeByAddress`, `searchPostOfficeByCoordinates`, `getPostOfficeServices`, `getPostalCodesInLocality`, `getPostOfficeFromPassport`, `returnShipment`, `createReturnShipment`, `deleteReturnShipment`, `editReturnShipment` | create/edit/find orders, batch ops, labels/docs, postoffice search, return shipments |
| `Tracking` | `getOperationsByRpo`, `getNpayInfo`, `getTickets`, `getOperationsByTicket` | операции по отслеживанию/трекингу и тикеты |

## Entities table
| Class | Public surface (main) | Notes |
|---|---|---|
| `AddressList` | `add`, `getIterator`, `count` | list wrapper for address cleansing payload |
| `FioList` | `add`, `getIterator`, `count` | list wrapper for fio cleansing payload |
| `PhoneList` | `add`, `getIterator`, `count` | list wrapper for phone cleansing payload |
| `CategoryList` | `setSubcategory`, `setDescription`, `setCategoryDelete`, `getSubcategory`, `getDescription`, `getCategoryDelete`, `parseToArray` | helper for tariff/category request payload |
| `ParcelInfo` | `getArray`, `is/set*` boolean&service flags | maps request fields for `getDeliveryTariff` |
| `Tariff` | getters for id/name/value/NDS/mark | model for tariff line in calculate response |
| `TariffInfo` | getters/setters for all charge + duration fields | tariff response DTO |
| `CalculateInfo` | typed and monetary helper getters/setters | value object for tariff output shaping |
| `StatusList` | `isFinal`, `getInfo` | helper for status code interpretation |
| `Order` | `asArr` + many `get/set` (recipient/address/parcel/payment/customs/ecom/etc) | main order payload DTO for create/edit/list operations |
| `ReturnShipment` | `asArr`, address/item fields for return flow | return shipment payload DTO |
| `AddressReturn` | `asArr`, address-part getters/setters | used by `ReturnShipment` |
| `CustomsDeclaration` | customs fields + items | nested inside `Order` |
| `CustomsDeclarationItem` | customs item fields | nested list item in `CustomsDeclaration` |
| `EcomData` | e-commerce rate/VAT/services | nested in `Order` |
| `Item` | item fields + duplicated Cyrillic-prefixed aliases in method names | nested in `Order::setItems` payload |
| `Recipient` | `getAddress/name/phone`, `getParams` | helper for `untrustworthyRecipient(s)` |

## Enums/dictionaries table
| Enum class | Constants |
|---|---|
| `AddressType` | `DEFAULT`, `PO_BOX`, `DEMAND`, `UNIT` |
| `DimensionType` | `S`, `M`, `L`, `XL`, `OVERSIZED` |
| `EntriesType` | `GIFT`, `DOCUMENT`, `SALE_OF_GOODS`, `COMMERCIAL_SAMPLE`, `OTHER` |
| `MailCategory` | `SIMPLE`, `ORDERED`, `ORDINARY`, `WITH_DECLARED_VALUE`, `WITH_DECLARED_VALUE_AND_CASH_ON_DELIVERY`, `WITH_DECLARED_VALUE_AND_COMPULSORY_PAYMENT`, `WITH_COMPULSORY_PAYMENT` |
| `MailType` | `UNDEFINED`, `PARCEL_POSTAL`, `PARCEL_ONLINE`, `PARCEL_CLASS_1`, `ONLINE_COURIER`, `BUSINESS_COURIER`, `BUSINESS_COURIER_ES`, `EMS`, `EMS_RT`, `EMS_OPTIMAL`, `EMS_TENDER`, `BANDEROL`, `BANDEROL_CLASS_1`, `LETTER`, `LETTER_CLASS_1`, `VSD`, `ECOM`, `COMBINED`, `EASY_RETURN`, `VGPO_CLASS_1`, `SMALL_PACKET` |
| `OpsObjectType` | `ALL`, `OPS`, `PVZ`, `APS` |
| `PaymentMethods` | `CASHLESS`, `STAMP`, `FRANKING`, `TO_FRANKING`, `ONLINE_PAYMENT_MARK` |
| `PostType` | numeric set for old Otpravka dictionary (e.g., `MAIL`, `POSILKA`, `EMS`, `EMS_OPTIMAL`, etc.) | large constant map |
| `TransportType` | `SURFACE`, `AVIA`, `COMBINED`, `EXPRESS`, `STANDARD` |
| `Recipient` (not enum class) | `RELIABLE`, `FRAUD` |

## Methods that match required Otpravka field/data needs
| Need | SDK method/entity support |
|---|---|
| Tariff calculation for checkout | `Calculation::getTariff`, `Calculation::getTariffAndDeliveryPeriod`, `ParcelInfo::getArray`, `getDeliveryTariff` |
| Order create/update | `OtpravkaApi::createOrdersV2`, `OtpravkaApi::editOrder`, `Order::asArr`, `Order` field set methods |
| Order lookup/status by shop id, internal id, RPO | `findOrderByShopId`, `findOrderById`, `findOrderByRpo`, `findOrderInBatch` |
| Batch workflow | `createBatch`, `addOrdersToBatch`, `getOrdersInBatch`, `moveOrdersToBatch`, `changeBatchSendingDay`, `archivingBatch`, `getArchivedBatches` |
| Label/print outputs | `generateDoc*`, `generateReturnLabel`, `sendingF103form`, `file` response support in `OtpravkaApi::callApi` |
| Recipient validation | `untrustworthyRecipient`, `untrustworthyRecipients`, `Recipient` + `clearAddress`/`clearPhone`/`clearFio` |
| Returns | `returnShipment`, `createReturnShipment`, `deleteReturnShipment`, `editReturnShipment`, `ReturnShipment::asArr`, `AddressReturn::asArr` |
| Post offices directory | `shippingPoints`, `settings`, `searchPostOfficeByIndex`, `searchPostOfficeByAddress`, `searchPostOfficeByCoordinates`, `getPostOfficeServices` |
| Tracking | `Tracking::getOperationsByRpo`, `getTickets`, `getOperationsByTicket`, `getNpayInfo` |

## Obvious gaps / blockers for thin-wrapper migration
- `Tracking` uses `ext-soap` and RussianPost SOAP WSDLs; thin wrapper that already uses REST clients must provide separate SOAP adapter or keep legacy path isolated.
- SDK transport is strict PSR-18/PSR-17 style (constructor DI). Existing wrapper likely must provide compatible transport factory + request/file factories.
- Mixed legacy and inconsistent naming in entities: several methods are non-typed and `Item` has duplicated Cyrillic-variant aliases (`getСountryCode`, `getCustomsDeclarationNumber`, etc.), which is risky for strict static tooling.
- `OtpravkaApi` exposes many convenience endpoints and legacy constants; not all are guaranteed used by current codebase, so wrapper should select only needed subset.
- HTTP responses for docs/tickets are parsed as `UploadedFileInterface` in-file responses, requiring a consistent uploaded-file factory in host runtime.
- `Tracking` has separate service mode switching (`single`/`pack`) with hidden max-size chunking for tickets (500), not obviously documented in thin wrapper callers.
