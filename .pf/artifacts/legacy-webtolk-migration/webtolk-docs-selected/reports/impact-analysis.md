# Impact Analysis

## Trigger
Transition from intake to investigation for rebuilding the Webtolk Russian Post library as a Joomla-native implementation based on LapayGroup functional coverage.

## Affected Components
- Future Joomla library extension package in this repository
- Future companion plugin that will store credentials and library settings
- Build and package metadata driven by `phing.xml` and `.webtolk/build/package.config.json`
- Historical references under `docs/Webtolk-joomla-library/`
- Donor reference under `docs/lapay-group-russian-post-library/`

## Domain Surface
- Russian Post account/settings access
- Shipping points and user settings
- Tariff and delivery calculations
- Address, FIO and phone normalization helpers
- Order lifecycle operations
- Batch lifecycle operations
- Document generation and downloadable forms
- Post office search and postal-code lookup
- Returns handling
- Tracking via SOAP
- Donor entities, enums and helper collections

## Runtime Surface
- Joomla library installation and namespace registration
- Plugin-based parameter retrieval
- Joomla HTTP client usage for REST endpoints
- PHP `SoapClient` usage for tracking endpoints
- Logging and exception handling
- Potential file-download handling for generated documents

## Data Or State Risks
- Joomla library extensions are effectively reinstalled on update, so extension params/custom data handling must be considered during release design.
- Credentials should not be split across incompatible storage models between plugin and library code.
- Donor entities may encourage over-modeling if copied blindly instead of minimized to actual internal needs.
- SOAP transport introduces separate runtime requirements from the REST layer.

## User-Facing Risks
- If method coverage from LapayGroup is mapped incompletely, the internal plugin may lack needed Russian Post operations.
- If the new API is over-abstracted, maintenance cost will increase without business benefit.
- If library packaging or plugin settings flow is designed incorrectly, installation and update behavior in Joomla may become fragile.

## Assurance Focus
- Verify every required LapayGroup capability is either mapped or explicitly deferred.
- Verify each planned custom abstraction against Joomla core before implementation.
- Verify REST transport can be implemented cleanly with Joomla HTTP facilities.
- Verify SOAP tracking requirements separately from REST assumptions.
- Verify manifest, namespace and packaging choices against Joomla library-extension rules.
- Verify the future plugin-library boundary for credentials and operational settings before coding begins.
