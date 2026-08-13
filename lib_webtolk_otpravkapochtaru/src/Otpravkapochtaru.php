<?php

/**
 * Joomla facade for the LapayGroup Russian Post SDK.
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

require_once __DIR__ . '/libraries/vendor/autoload.php';

use LapayGroup\RussianPost\Http\Psr18Transport;
use LapayGroup\RussianPost\Providers\Calculation;
use LapayGroup\RussianPost\Providers\OtpravkaApi;
use LapayGroup\RussianPost\Providers\Tracking;
use Webtolk\Otpravkapochtaru\Joomla\CredentialsProvider;
use Webtolk\Otpravkapochtaru\Joomla\Psr18TransportFactory;

/**
 * Thin Joomla facade for configured LapayGroup Russian Post clients.
 *
 * The facade resolves Joomla plugin parameters, builds the PSR-18 transport and
 * exposes ready SDK providers. Domain operations should be performed through
 * the returned LapayGroup providers, while this class keeps only Joomla Form
 * convenience helpers used by the bundled fields.
 *
 * @since  0.1.0
 */
final class Otpravkapochtaru
{
    /**
     * Single-item SOAP tracking service identifier.
     *
     * @since  3.0.0
     */
    private const TRACKING_SERVICE_SINGLE = 'single';

    /**
     * Credentials resolver for plugin and explicit configuration sources.
     *
     * @since  3.0.0
     */
    private CredentialsProvider $credentialsProvider;

    /**
     * PSR-18 transport adapter built on Joomla HTTP.
     *
     * @since  3.0.0
     */
    private Psr18Transport $transport;

    /**
     * Upstream REST API client.
     *
     * @since  3.0.0
     */
    private OtpravkaApi $otpravkaApi;

    /**
     * Upstream tariff API client.
     *
     * @since  3.0.0
     */
    private Calculation $calculation;

    /**
     * Lazily created SOAP tracking API client.
     *
     * @since  3.0.0
     */
    private ?Tracking $trackingApi = null;

    /**
     * Build configured LapayGroup SDK clients from Joomla plugin params or an explicit source.
     *
     * @param   CredentialsProvider|array<string, mixed>|object|null  $credentialsSource  Explicit configuration source.
     *
     * @throws  \RuntimeException
     *
     * @since   3.0.0
     */
    public function __construct(array|object|null $credentialsSource = null)
    {
        $this->credentialsProvider = $this->resolveCredentialsProvider($credentialsSource);

        $this->transport = Psr18TransportFactory::create(
            $this->credentialsProvider->getHttpTimeout()
        );

        $this->otpravkaApi = new OtpravkaApi($this->resolveOtpravkaConfig(), $this->transport);
        $this->calculation = new Calculation($this->transport);
    }

    /**
     * Return the credentials provider used by this facade.
     *
     * @return  CredentialsProvider
     *
     * @since   3.0.0
     */
    public function credentialsProvider(): CredentialsProvider
    {
        return $this->credentialsProvider;
    }

    /**
     * Return the configured PSR-18 transport.
     *
     * @return  Psr18Transport
     *
     * @since   3.0.0
     */
    public function transport(): Psr18Transport
    {
        return $this->transport;
    }

    /**
     * Return the configured LapayGroup REST API provider.
     *
     * @return  OtpravkaApi
     *
     * @since   3.0.0
     */
    public function otpravkaApi(): OtpravkaApi
    {
        return $this->otpravkaApi;
    }

    /**
     * Return the configured LapayGroup tariff provider.
     *
     * @return  Calculation
     *
     * @since   3.0.0
     */
    public function calculation(): Calculation
    {
        return $this->calculation;
    }

    /**
     * Return the configured LapayGroup SOAP tracking provider.
     *
     * @return  Tracking
     *
     * @throws  \RuntimeException
     *
     * @since   3.0.0
     */
    public function trackingApi(): Tracking
    {
        if ($this->trackingApi instanceof Tracking) {
            return $this->trackingApi;
        }

        if ($this->credentialsProvider->getTrackingLogin() === '' || $this->credentialsProvider->getTrackingPassword() === '') {
            throw new \RuntimeException(
                'Tracking credentials are not configured. Fill tracking_login and tracking_password to use SOAP tracking methods.'
            );
        }

        try {
            $this->trackingApi = new Tracking(
                self::TRACKING_SERVICE_SINGLE,
                $this->resolveTrackingConfig(),
                max(1, $this->credentialsProvider->getHttpTimeout())
            );
        } catch (\Throwable $exception) {
            throw new \RuntimeException(
                'Tracking service is unavailable. Ensure SOAP extension and tracking credentials are configured.',
                0,
                $exception
            );
        }

        return $this->trackingApi;
    }

    /**
     * Load account settings for Joomla Form information fields.
     *
     * @return  array<string, mixed>
     *
     * @throws  \LapayGroup\RussianPost\Exceptions\RussianPostException
     *
     * @since   3.0.0
     */
    public function getAccountInfo(): array
    {
        return $this->otpravkaApi->settings();
    }

    /**
     * Load shipment points for Joomla Form list fields.
     *
     * @return  array<int, array<string, mixed>>
     *
     * @throws  \LapayGroup\RussianPost\Exceptions\RussianPostException
     *
     * @since   3.0.0
     */
    public function getShippingPoints(): array
    {
        return $this->otpravkaApi->shippingPoints();
    }

    /**
     * Load API request limits for Joomla Form information fields.
     *
     * @return  array<string, mixed>
     *
     * @throws  \JsonException
     * @throws  \Psr\Http\Client\ClientExceptionInterface
     * @throws  \RuntimeException
     *
     * @since   3.0.0
     */
    public function getApiLimit(): array
    {
        return $this->getLegacyEndpoint('settings/limit');
    }

    /**
     * Resolve an explicit source to the Joomla credentials provider.
     *
     * @param   CredentialsProvider|array<string, mixed>|object|null  $credentialsSource  Explicit configuration source.
     *
     * @return  CredentialsProvider
     *
     * @since   3.0.0
     */
    private function resolveCredentialsProvider(array|object|null $credentialsSource): CredentialsProvider
    {
        if ($credentialsSource instanceof CredentialsProvider) {
            return $credentialsSource;
        }

        return new CredentialsProvider($credentialsSource);
    }

    /**
     * Build upstream otpravka authorization config.
     *
     * @return  array<string, mixed>
     *
     * @throws  \RuntimeException
     *
     * @since   3.0.0
     */
    private function resolveOtpravkaConfig(): array
    {
        return [
            'auth' => [
                'otpravka' => [
                    'token' => $this->credentialsProvider->getAccessToken(),
                    'key'   => $this->credentialsProvider->getUserAuthorizationHeader(),
                ],
            ],
        ];
    }

    /**
     * Build upstream tracking authorization config.
     *
     * @return  array<string, mixed>
     *
     * @since   3.0.0
     */
    private function resolveTrackingConfig(): array
    {
        return [
            'auth' => [
                'tracking' => [
                    'login'    => $this->credentialsProvider->getTrackingLogin(),
                    'password' => $this->credentialsProvider->getTrackingPassword(),
                ],
            ],
        ];
    }

    /**
     * Build REST authorization headers for Joomla-only helper calls.
     *
     * @return  array<string, string>
     *
     * @throws  \RuntimeException
     *
     * @since   3.0.0
     */
    private function restHeaders(): array
    {
        return [
            'Authorization'        => 'AccessToken ' . $this->credentialsProvider->getAccessToken(),
            'X-User-Authorization' => 'Basic ' . $this->credentialsProvider->getUserAuthorizationHeader(),
            'Content-Type'         => 'application/json',
            'Accept'               => 'application/json;charset=UTF-8',
        ];
    }

    /**
     * Fetch a REST path that is used by Joomla helper fields and absent from the SDK provider.
     *
     * @param   string  $path  API path relative to `/1.0/`.
     *
     * @return  array<string, mixed>
     *
     * @throws  \JsonException
     * @throws  \Psr\Http\Client\ClientExceptionInterface
     * @throws  \RuntimeException
     *
     * @since   3.0.0
     */
    private function getLegacyEndpoint(string $path): array
    {
        $response = $this->transport->send(
            'GET',
            'https://otpravka-api.pochta.ru/1.0/' . ltrim($path, '/'),
            $this->restHeaders(),
        );

        $body = $response->getBody();
        if ($body->isSeekable()) {
            $body->rewind();
        }

        $rawBody = (string) $body->getContents();
        $statusCode = $response->getStatusCode();

        if ($statusCode >= 400) {
            throw new \RuntimeException(sprintf('REST GET /1.0/%s failed with status %d.', $path, $statusCode), $statusCode);
        }

        if (trim($rawBody) === '') {
            throw new \RuntimeException(sprintf('REST GET /1.0/%s returned empty response body.', $path), $statusCode);
        }

        $decoded = json_decode($rawBody, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($decoded)) {
            throw new \RuntimeException(sprintf('REST GET /1.0/%s returned non-array response.', $path), $statusCode);
        }

        return $decoded;
    }
}
