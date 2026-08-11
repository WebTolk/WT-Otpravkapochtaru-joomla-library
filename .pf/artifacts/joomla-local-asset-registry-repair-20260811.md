# Joomla Local Asset Registry Repair - 2026-08-11

## Scope

- Incident URL: `http://joomla.local/administrator/index.php?option=com_plugins&view=plugin&layout=edit&extension_id=389`
- Reported error: `Asset registry file "media\lib_wt_otpravkapochtaru\joomla.asset.json" contains invalid JSON.`
- Agent/role: Codex / local Joomla smoke and ProcessForge recovery
- Product source changed: no
- Local Joomla stand changed: yes, restored one corrupted installed media file

## Findings

- Source asset registry: `lib_webtolk_otpravkapochtaru/media/joomla.asset.json`
  - size: `515`
  - SHA-256: `32D44825B44929CBB238801DC55EF1619FDA1968C3ACB6CA6910B1AE0745DF3E`
  - JSON parse: pass
- Installed stand asset registry before repair:
  - path: `D:\OSPanel\home\joomla.local\public\media\lib_wt_otpravkapochtaru\joomla.asset.json`
  - size: `515`
  - content symptom: NUL-byte corrupted file
  - JSON parse error: `Invalid JSON primitive: .`
- Release archives:
  - `dist/WT-Otpravkapochtaru-Joomla-library_3.0.0.zip`: asset entry `JSON_OK`, size `515`
  - `.packages/WT Otpravkapochtaru_3.0.0.zip`: asset entry `JSON_OK`, size `515`

## Repair

Copied the valid source file over the corrupted stand file:

```powershell
$src = Resolve-Path 'lib_webtolk_otpravkapochtaru\media\joomla.asset.json'
$dst = 'D:\OSPanel\home\joomla.local\public\media\lib_wt_otpravkapochtaru\joomla.asset.json'
Copy-Item -LiteralPath $src -Destination $dst -Force
```

Post-repair evidence:

- installed stand file JSON parse: pass
- installed stand file SHA-256: `32D44825B44929CBB238801DC55EF1619FDA1968C3ACB6CA6910B1AE0745DF3E`
- source and installed stand file hashes match

## Browser Smoke

- Logged into `joomla.local/administrator` with the local Codex Joomla credentials.
- Opened the reported URL and the canonical Joomla plugin edit route for the same id.
- Result:
  - no visible `Asset registry file` message
  - no visible `invalid JSON` message
  - browser console only showed Cross-Origin-Opener-Policy warnings caused by HTTP origin
- Residual observation:
  - Joomla redirects `extension_id=389` back to the plugin list instead of opening an edit form.
  - Earlier snapshot showed `You are not permitted to use that link to directly access that page (#389)`.
  - CLI DB lookup could not be completed because local CLI PHP has neither `mysqli` nor PDO drivers.

## Worker State

- No active `process-forge` / `shell-worker` / `.pf` worker process was found after narrowing the process filter.
- The only matching process in the final check was the one-off PowerShell diagnostic command itself.

## Verdict

The asset registry failure was caused by a corrupted installed file on the local Joomla stand, not by the repository source or release archives. The stand file has been restored and the reported JSON parser failure is no longer reproduced in the browser.
