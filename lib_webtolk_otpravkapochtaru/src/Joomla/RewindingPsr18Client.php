<?php

/**
 * PSR-18 client decorator that normalizes response body cursor position.
 *
 * @package     WT Otpravkapochtaru
 * @version     3.0.0
 * @author      Sergey Tolkachyov
 * @copyright   Copyright (c) 2026 Sergey Tolkachyov, WebTolk. All rights reserved.
 * @license     GNU/GPL 3.0
 * @since       3.0.0
 */

namespace Webtolk\Otpravkapochtaru\Joomla;

defined('_JEXEC') or die;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Joomla HTTP may return PSR-7 streams with the cursor already at EOF.
 *
 * @since  3.0.0
 */
final class RewindingPsr18Client implements ClientInterface
{
    /**
     * Decorate the underlying PSR-18 client.
     *
     * @param   ClientInterface  $client  HTTP client to decorate.
     *
     * @since   3.0.0
     */
    public function __construct(private readonly ClientInterface $client)
    {
    }

    /**
     * Send a request and rewind a seekable response body.
     *
     * @param   RequestInterface  $request  PSR-7 request.
     *
     * @return  ResponseInterface
     *
     * @throws  ClientExceptionInterface
     *
     * @since   3.0.0
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $response = $this->client->sendRequest($request);
        $body     = $response->getBody();

        if ($body->isSeekable()) {
            $body->rewind();
        }

        return $response;
    }
}
