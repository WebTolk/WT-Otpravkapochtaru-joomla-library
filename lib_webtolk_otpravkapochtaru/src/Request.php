<?php

/**
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

    public function __construct(private readonly CredentialsProvider $credentialsProvider)
    {
    }

    /**
     * @param array<string, scalar|null> $query
     *
     * @return array<string, mixed>
     */
    public function get(string $path, array $query = [], string $endpoint = self::ENDPOINT_OTPRAVKA): array
    {
        $response = $this->http()->get($this->buildRequestTarget($path, $endpoint, $query), $this->headers());

        return $this->decodeResponse($response, 'GET', $path);
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, scalar|null> $query
     *
     * @return array<string, mixed>
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
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
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
     * @param array<string, mixed> $payload
     *
     * @return array<string, mixed>
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
     * @param array<string, scalar|null> $query
     *
     * @return array<string, mixed>
     */
    public function delete(string $path, array $query = [], string $endpoint = self::ENDPOINT_OTPRAVKA): array
    {
        $response = $this->http()->delete($this->buildRequestTarget($path, $endpoint, $query), $this->headers());

        return $this->decodeResponse($response, 'DELETE', $path);
    }

    /**
     * @param array<string, scalar|null> $query
     *
     * @return array{
     *     content: string,
     *     contentType: string,
     *     fileName: string|null,
     *     statusCode: int,
     *     headers: array<string, mixed>
     * }
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

    private function http(): object
    {
        return (new HttpFactory())->getHttp(['timeout' => $this->credentialsProvider->getHttpTimeout()], ['curl', 'stream']);
    }

    /**
     * @return array<string, string>
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
     * @param array<string, scalar|null> $query
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
     * @return array<string, mixed>
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
     * @param array<string, mixed> $payload
     */
    private function encodePayload(array $payload, string $method, string $path): string
    {
        $encodedPayload = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if ($encodedPayload === false) {
            throw new TransportException(sprintf('%s %s payload encoding failed.', $method, $path));
        }

        return $encodedPayload;
    }

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

    private function sanitizeFileName(string $fileName): string
    {
        $value = trim($fileName, "\"'");

        return $value === '' ? 'document' : $value;
    }

    /**
     * @param array<string, mixed> $decoded
     */
    private function hasBusinessError(array $decoded): bool
    {
        if (isset($decoded['status']) && is_string($decoded['status']) && strtoupper($decoded['status']) === 'ERROR') {
            return true;
        }

        return isset($decoded['code']) || isset($decoded['error-code']) || isset($decoded['error']);
    }

    /**
     * @param array<string, mixed> $decoded
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
