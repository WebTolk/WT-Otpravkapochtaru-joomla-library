<?php

/**
 * Low-level REST transport for Russian Post JSON and binary endpoints.
 *
 * @package     WT Otpravkapochtaru
 * @version     3.0.0
 * @author      Sergey Tolkachyov
 * @copyright   Copyright (c) 2026 Sergey Tolkachyov, WebTolk. All rights reserved.
 * @license     GNU/GPL 3.0
 * @since       0.1.0
 */

namespace Webtolk\Otpravkapochtaru;

defined('_JEXEC') or die;

use Joomla\CMS\Http\HttpFactory;
use Joomla\Http\Response;
use Joomla\Uri\Uri;
use Webtolk\Otpravkapochtaru\Configuration\CredentialsProvider;
use Webtolk\Otpravkapochtaru\Exception\TransportException;

final class Request
{
    public const ENDPOINT_OTPRAVKA   = 'otpravka';
    public const ENDPOINT_DELIVERY   = 'delivery';
    public const ENDPOINT_POSTOFFICE = 'postoffice';

    private const BASE_URIS = [
        self::ENDPOINT_OTPRAVKA   => 'https://otpravka-api.pochta.ru',
        self::ENDPOINT_DELIVERY   => 'https://delivery.pochta.ru/delivery',
        self::ENDPOINT_POSTOFFICE => 'https://otpravka-api.pochta.ru/postoffice',
    ];

    /**
     * Store credentials used for headers, timeout and endpoint calls.
     *
     * @since 3.0.0
     */
    public function __construct(private readonly CredentialsProvider $credentialsProvider)
    {
    }

    /**
     * Execute a JSON GET request and decode the Russian Post response as an array.
     *
     * @param array<string, scalar|null> $query
     *
     * @return array<string, mixed>
     *
     * @since 3.0.0
     */
    public function get(string $path, array $query = [], string $endpoint = self::ENDPOINT_OTPRAVKA): array
    {
        $response = $this->http()->get($this->buildRequestTarget($path, $endpoint, $query), $this->headers());

        return $this->decodeResponse($response, 'GET', $path);
    }

    /**
     * Execute a JSON POST request with optional query parameters.
     *
     * @param array<string, mixed> $payload
     * @param array<string, scalar|null> $query
     *
     * @return array<string, mixed>
     *
     * @since 3.0.0
     */
    public function postJson(
        string $path,
        array $payload,
        string $endpoint = self::ENDPOINT_OTPRAVKA,
        array $query = []
    ): array {
        $response = $this->http()->post(
            $this->buildRequestTarget($path, $endpoint, $query),
            $this->encodePayload($payload, 'POST', $path),
            $this->headers()
        );

        return $this->decodeResponse($response, 'POST', $path);
    }

    /**
     * Execute a JSON PUT request against a Russian Post endpoint.
     *
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     *
     * @since 3.0.0
     */
    public function putJson(string $path, array $payload, string $endpoint = self::ENDPOINT_OTPRAVKA): array
    {
        $response = $this->http()->put(
            $this->buildUri($path, $endpoint),
            $this->encodePayload($payload, 'PUT', $path),
            $this->headers()
        );

        return $this->decodeResponse($response, 'PUT', $path);
    }

    /**
     * Execute a DELETE request with a JSON request body.
     *
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
     *
     * @since 3.0.0
     */
    public function deleteJson(string $path, array $payload, string $endpoint = self::ENDPOINT_OTPRAVKA): array
    {
        $response = $this->http()->delete(
            $this->buildUri($path, $endpoint),
            $this->headers(),
            null,
            $this->encodePayload($payload, 'DELETE', $path)
        );

        return $this->decodeResponse($response, 'DELETE', $path);
    }

    /**
     * Execute a DELETE request whose parameters are sent in the query string.
     *
     * @param array<string, scalar|null> $query
     *
     * @return array<string, mixed>
     *
     * @since 3.0.0
     */
    public function delete(string $path, array $query = [], string $endpoint = self::ENDPOINT_OTPRAVKA): array
    {
        $response = $this->http()->delete($this->buildRequestTarget($path, $endpoint, $query), $this->headers());

        return $this->decodeResponse($response, 'DELETE', $path);
    }

    /**
     * Download a binary document and return its body, content type, optional file name and raw headers.
     *
     * @param array<string, scalar|null> $query
     *
     * @return array{
     *     content: string,
     *     contentType: string,
     *     fileName: string|null,
     *     statusCode: int,
     *     headers: array<string, mixed>
     * }
     *
     * @since 3.0.0
     */
    public function getBinary(string $path, array $query = [], string $endpoint = self::ENDPOINT_OTPRAVKA): array
    {
        $response   = $this->http()->get($this->buildRequestTarget($path, $endpoint, $query), $this->headers());
        $statusCode = $response->getStatusCode();

        if ($statusCode >= 400) {
            throw new TransportException(sprintf('GET %s failed with status %d.', $path, $statusCode), $statusCode);
        }

        return [
            'content'     => (string) $response->getBody(),
            'contentType' => $this->normalizeContentType($response->getHeaders()['Content-Type'] ?? $response->getHeaders()['content-type'] ?? 'application/octet-stream'),
            'fileName'    => $this->extractFileName($response->getHeaders()['Content-Disposition'] ?? $response->getHeaders()['content-disposition'] ?? null),
            'statusCode'  => $statusCode,
            'headers'     => $response->getHeaders(),
        ];
    }

    /**
     * Create a Joomla HTTP client with the timeout configured in plugin/library parameters.
     *
     * @since 3.0.0
     */
    private function http(): object
    {
        return (new HttpFactory())->getHttp(['timeout' => $this->credentialsProvider->getHttpTimeout()], ['curl', 'stream']);
    }

    /**
     * Build authentication and JSON headers required by the Otpravka API.
     *
     * @return array<string, string>
     *
     * @since 3.0.0
     */
    private function headers(): array
    {
        return [
            'Authorization'        => 'AccessToken ' . $this->credentialsProvider->getAccessToken(),
            'X-User-Authorization' => 'Basic ' . $this->credentialsProvider->getUserAuthorizationHeader(),
            'Content-Type'         => 'application/json',
            'Accept'               => 'application/json;charset=UTF-8',
        ];
    }

    /**
     * Build an absolute endpoint URI and preserve a base path such as `/postoffice`.
     *
     * @since 3.0.0
     */
    private function buildUri(string $path, string $endpoint): Uri
    {
        if (!isset(self::BASE_URIS[$endpoint])) {
            throw new TransportException(sprintf('Unknown endpoint \"%s\".', $endpoint));
        }

        $uri         = new Uri(self::BASE_URIS[$endpoint]);
        $basePath    = trim((string) $uri->getPath(), '/');
        $requestPath = trim($path, '/');
        $fullPath    = $basePath !== '' ? $basePath . '/' . $requestPath : $requestPath;
        $uri->setPath('/' . $fullPath);

        return $uri;
    }

    /**
     * Build a request URL with normalized scalar query values.
     *
     * Null values are skipped and booleans are encoded as API-friendly `true`/`false` strings.
     *
     * @param array<string, scalar|null> $query
     *
     * @since 3.0.0
     */
    private function buildRequestTarget(string $path, string $endpoint, array $query = []): string
    {
        $uri        = $this->buildUri($path, $endpoint);
        $normalized = [];

        foreach ($query as $key => $value) {
            if ($value === null) {
                continue;
            }

            if (is_bool($value)) {
                $normalized[$key] = $value ? 'true' : 'false';
                continue;
            }

            $normalized[$key] = (string) $value;
        }

        $target = $uri->toString(['scheme', 'user', 'pass', 'host', 'port', 'path']);

        if ($normalized === []) {
            return $target;
        }

        return $target . '?' . http_build_query($normalized, '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * Decode a JSON response and turn HTTP or business-level API errors into TransportException.
     *
     * Russian Post can return successful HTTP statuses with error markers in the JSON body, so both
     * transport status and decoded payload keys are checked before the array is returned.
     *
     * @return array<string, mixed>
     *
     * @since 3.0.0
     */
    private function decodeResponse(Response $response, string $method, string $path): array
    {
        $decoded    = json_decode((string) $response->getBody(), true);
        $statusCode = $response->getStatusCode();

        if (!is_array($decoded)) {
            throw new TransportException(sprintf('%s %s returned a non-JSON or non-array response.', $method, $path), $statusCode);
        }

        $message = $this->extractErrorMessage($decoded);

        if ($statusCode >= 400) {
            throw new TransportException(sprintf('%s %s failed: %s', $method, $path, $message), $statusCode);
        }

        if ($this->hasBusinessError($decoded)) {
            throw new TransportException(sprintf('%s %s failed: %s', $method, $path, $message), $statusCode);
        }

        return $decoded;
    }

    /**
     * Encode request payload with Russian text preserved for API diagnostics.
     *
     * @param array<string, mixed> $payload
     *
     * @since 3.0.0
     */
    private function encodePayload(array $payload, string $method, string $path): string
    {
        $encodedPayload = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($encodedPayload === false) {
            throw new TransportException(sprintf('%s %s payload encoding failed.', $method, $path));
        }

        return $encodedPayload;
    }

    /**
     * Reduce a Content-Type header value to the media type used by callers.
     *
     * @since 3.0.0
     */
    private function normalizeContentType(mixed $header): string
    {
        if (is_array($header)) {
            $header = $header[0] ?? '';
        }

        if (!is_string($header) || $header === '') {
            return 'application/octet-stream';
        }

        $parts = explode(';', strtolower($header));

        return trim($parts[0]);
    }

    /**
     * Extract an attachment file name from a Content-Disposition header.
     *
     * Supports both RFC 5987 UTF-8 `filename*` and regular `filename` forms.
     *
     * @since 3.0.0
     */
    private function extractFileName(mixed $header): ?string
    {
        if (is_array($header)) {
            $header = $header[0] ?? null;
        }

        if (!is_string($header) || $header === '') {
            return null;
        }

        if (preg_match('/filename\*=UTF-8\'\'([^;]+)/i', $header, $matches)) {
            return $this->sanitizeFileName(urldecode($matches[1]));
        }

        if (preg_match('/filename="?([^";]+)"?/i', $header, $matches)) {
            return $this->sanitizeFileName($matches[1]);
        }

        return null;
    }

    /**
     * Trim quotes from a server-provided file name and fall back to a stable generic name.
     *
     * @since 3.0.0
     */
    private function sanitizeFileName(string $fileName): string
    {
        $value = trim($fileName, "\"'");

        return $value === '' ? 'document' : $value;
    }

    /**
     * Detect Russian Post business-error markers in an otherwise decoded JSON payload.
     *
     * @param array<string, mixed> $decoded
     *
     * @since 3.0.0
     */
    private function hasBusinessError(array $decoded): bool
    {
        if (isset($decoded['status']) && is_string($decoded['status']) && strtoupper($decoded['status']) === 'ERROR') {
            return true;
        }

        return isset($decoded['code']) || isset($decoded['error-code']) || isset($decoded['error']);
    }

    /**
     * Pick the most useful error text from known Russian Post response keys.
     *
     * @param array<string, mixed> $decoded
     *
     * @since 3.0.0
     */
    private function extractErrorMessage(array $decoded): string
    {
        foreach (['message', 'desc', 'sub-code', 'error', 'error-code', 'code', 'status'] as $key) {
            if (!isset($decoded[$key])) {
                continue;
            }

            $value = $decoded[$key];

            if (is_scalar($value) && (string) $value !== '') {
                return (string) $value;
            }
        }

        return 'Unknown transport error.';
    }
}
