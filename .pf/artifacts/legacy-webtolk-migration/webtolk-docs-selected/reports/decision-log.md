# Decision Log

## Revision Decision
Use live `joomla.local` transport evidence as the canonical source for the non-tracking public facade, with `Lapay Group` retained as a donor reference but not as a source of current endpoint truth.

## Context
- The completed non-tracking sweep on `joomla.local` produced a full dump set and raw transport log.
- The donor library still contains methods that point to dead delivery/balance endpoints.
- The project explicitly does not require donor backward compatibility.

## Options Considered
1. Keep dead donor-era methods as explicit unsupported stubs.
2. Remove dead donor-era methods from the public facade and keep only verified live mappings.
3. Delay the decision until an external official replacement specification appears.

## Chosen Direction
Option 2.

## Working Rules
- Live `joomla.local` dumps are the source of truth for transport behavior.
- `Lapay Group` remains a structural and historical donor reference only.
- A method may stay in the public facade only if it has a justified current role:
  - live API confirms the mapping; or
  - the method serves stable official local reference data needed by the product.
- If a donor-era method has no current contract and no product need beyond legacy parity, remove it instead of preserving it as an unsupported runtime stub.

## Consequences
- `getShippingPoints()` and the postoffice methods remain because they are now live-verified.
- `getCountryList()` remains as local official reference data.
- `getBalance()`, `getCategoryList()`, `getCategoryDescription()`, and `getObjectInfo()` are removed from the public facade.
- The read-only sweep baseline is reduced to the real supported surface.

## Revisit Trigger
- A current official Otpravka REST specification adds replacement endpoints for the removed methods.
- Product requirements explicitly reintroduce donor-era compatibility as a goal.
