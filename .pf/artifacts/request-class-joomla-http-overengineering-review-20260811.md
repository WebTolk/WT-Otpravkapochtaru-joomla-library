# Request Class vs Joomla Http Review

Timestamp: 2026-08-11

## Scope

Review `lib_webtolk_otpravkapochtaru/src/Request.php` for overengineering and
duplicate checks already handled by Joomla Http/Uri.

This is an investigation artifact. Product code was not changed.

## Process Forge Context

- Project mode: `.pf` file-only ProcessForge project.
- Platform: Joomla fullstack.
- Relevant knowledge stack: `docs.joomla-core.v6-1-2`, `docs.joomla-toolkit`.
- Local Joomla core reference used: Joomla Framework `joomla/http` and
  `joomla/uri` from the Joomla 6.1.2 core snapshot.

## Request Class Map

`Request` has public transport methods:

- `get()`
- `postJson()`
- `putJson()`
- `deleteJson()`
- `delete()`
- `getBinary()`

Internal helpers:

- `http()`
- `headers()`
- `buildUri()`
- `buildRequestTarget()`
- `decodeResponse()`
- `encodePayload()`
- `normalizeContentType()`
- `extractFileName()`
- `sanitizeFileName()`
- `hasBusinessError()`
- `extractErrorMessage()`

## Joomla Core Comparison

Joomla Framework `Http` already handles:

- creating a transport-backed request from a string or `UriInterface`;
- converting string URL input to `Joomla\Uri\Uri`;
- applying client `headers` options;
- applying client `timeout` options;
- sending `GET`, `POST`, `PUT`, `DELETE`, `PATCH`, etc.;
- form-encoding non-scalar request data when no JSON string is supplied;
- returning a `Joomla\Http\Response` object.

Joomla Framework `Uri` handles:

- parsing a URI;
- storing and rendering URI parts;
- cleaning path segments when `setPath()` is used;
- storing query variables through `setQuery()` / `setVar()`.

Important observed difference:

- `Joomla\Uri\AbstractUri::buildQuery()` returns
  `urldecode(http_build_query($params, '', '&'))`.
- A runtime check showed `Uri::setQuery(['a' => 'x y', 'b' => true,
  'c' => 'тест/1'])` renders as `?a=x y&b=1&c=тест/1`.
- Current `Request::buildRequestTarget()` renders query with
  `http_build_query(..., PHP_QUERY_RFC3986)`, producing encoded spaces,
  UTF-8 bytes and slash, and normalizes booleans to `true` / `false`.

Therefore query construction should not be blindly delegated to Joomla `Uri`
without changing URL semantics.

## Findings

### `buildUri()`

Verdict: keep the responsibility, remove the separate method.

The method is not fully redundant because it validates endpoint aliases and
preserves endpoint base paths such as `/delivery` and `/postoffice`.
Joomla `Http` does not know the project's endpoint aliases.

However, `buildUri()` is only an internal helper used by `putJson()`,
`deleteJson()`, and `buildRequestTarget()`. It creates a `Uri` object while most
callers ultimately need a full request target string. Keeping both
`buildUri()` and `buildRequestTarget()` splits one concept into two methods.

Recommended refactor:

- replace `buildUri()` + `buildRequestTarget()` with one private method, for
  example `buildRequestUrl(string $path, string $endpoint, array $query = []):
  string`;
- use it for every HTTP method, including `putJson()` and `deleteJson()`;
- keep endpoint validation and base-path preservation inside that single
  method;
- keep the current query normalization and RFC3986 encoding.

### `normalizeContentType()`

Verdict: probably removable as a separate method.

Joomla response extends a PSR response implementation, so callers can use
`getHeaderLine('Content-Type')` instead of reading both `Content-Type` and
`content-type` from `getHeaders()`. A tiny media-type split may still be needed,
but it can be inline in `getBinary()` or hidden in a narrower helper.

### `extractFileName()` and `sanitizeFileName()`

Verdict: keep.

Joomla Http does not extract RFC 5987 `filename*` values from
`Content-Disposition`, and it does not sanitize server-provided filenames for
cross-platform use.

### `encodePayload()`

Verdict: keep.

Joomla Http transport will form-encode non-scalar data by default if an array is
passed. The API expects JSON payloads, so `Request` must encode JSON explicitly
and fail when encoding fails.

### `decodeResponse()`, `hasBusinessError()`, `extractErrorMessage()`

Verdict: keep.

Joomla Http returns transport response objects. It does not decode Russian Post
JSON payloads, detect API-level business errors in HTTP 2xx responses, or choose
Russian Post error text fields.

### `http()` and `headers()`

Verdict: acceptable.

They are small local factories for credentials, timeout and headers. They are
not duplicated Joomla validation. A future refactor may inject `Http` for
testing, but that is a design choice, not a release blocker.

## Recommendation

Do a narrow refactor only:

1. Replace `buildUri()` and `buildRequestTarget()` with a single private URL
   builder.
2. Route all HTTP methods through that builder.
3. Keep RFC3986 query encoding and boolean string normalization.
4. Use `Response::getHeaderLine()` in `getBinary()` and remove the case-folding
   header lookup.
5. Keep JSON encoding, API response decoding, business-error checks and filename
   sanitization.

This removes the real overengineering without outsourcing API-specific behavior
to Joomla core in places where Joomla does not provide equivalent semantics.
