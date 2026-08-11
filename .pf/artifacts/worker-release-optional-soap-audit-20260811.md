verdict: needs-fix

evidence:
- [composer.json](composer.json): only required extensions are `ext-mbstring` and `ext-simplexml`; `ext-soap` is not required in runtime dependencies.
- [lib_webtolk_otpravkapochtaru/src/Request.php:54](lib_webtolk_otpravkapochtaru/src/Request.php:54): REST transport class does not refer to `SoapRequest` or `TrackingEntity`.
- [lib_webtolk_otpravkapochtaru/src/Request.php:54-112](lib_webtolk_otpravkapochtaru/src/Request.php:54-112): Request methods are REST-only HTTP calls and binary helpers; no SOAP client construction.
- [lib_webtolk_otpravkapochtaru/src/Otpravkapochtaru.php:43](lib_webtolk_otpravkapochtaru/src/Otpravkapochtaru.php:43): SOAP tracking helper property is initialized on facade object construction.
- [lib_webtolk_otpravkapochtaru/src/Otpravkapochtaru.php:55](lib_webtolk_otpravkapochtaru/src/Otpravkapochtaru.php:55): facade constructor always executes `new TrackingEntity(new SoapRequest($credentialsProvider))`, so creating the main API facade for REST methods (`createOrders`, `getTariff`, `getTariffAndDeliveryPeriod`) instantiates SOAP-related objects.
- [lib_webtolk_otpravkapochtaru/src/Otpravkapochtaru.php:498-530](lib_webtolk_otpravkapochtaru/src/Otpravkapochtaru.php:498-530): SOAP methods are isolated here, but entry-point initialization is not deferred.
- [lib_webtolk_otpravkapochtaru/src/TrackingEntity.php:20](lib_webtolk_otpravkapochtaru/src/TrackingEntity.php:20): tracking methods and SOAP client creation are in `TrackingEntity`.
- [lib_webtolk_otpravkapochtaru/src/TrackingEntity.php:144-154](lib_webtolk_otpravkapochtaru/src/TrackingEntity.php:144-154): SOAP-only client factories are only used inside tracking paths (`\SoapClient`).
- [lib_webtolk_otpravkapochtaru/src/SoapRequest.php:46,62,101](lib_webtolk_otpravkapochtaru/src/SoapRequest.php:46,62,101): SOAP-specific clients are created in SOAP methods only.
- [docs/api/tracking.md:3](docs/api/tracking.md:3): docs state SOAP tracking is separate and requires SOAP credentials; REST credentials do not cover it.
- [README.md:28-30](README.md:28-30): readme explicitly says REST token/use and SOAP optional for tracking.

blocked_lines:
- No direct test coverage found for instantiation paths in the requested read scope. Searches of `tests/Unit/*` for direct loads of `Request`, `SoapRequest`, `TrackingEntity`, or `Otpravkapochtaru` returned no matches.

residual_risk:
- Because SOAP-related classes are eagerly instantiated in the shared facade constructor, environments without `ext-soap` may fail before tracking methods are called. This risk is unconfirmed without running a runtime test under PHP without SOAP extension (read-only audit scope).
