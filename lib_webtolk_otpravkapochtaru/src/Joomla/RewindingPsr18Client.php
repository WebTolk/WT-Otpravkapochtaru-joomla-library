<?php

/**
 * PSR-18 client decorator that normalizes response body cursor position.
 *
 * @package     WT Otpravkapochtaru
 * @version     3.0.0
 * @author      Sergey Tolkachyov
 * @copyright   Copyright (c) 2026 Sergey Tolkachyov, WebTolk. All rights reserved.
 * @license     GNU/GPL 3.0
 */

namespace Webtolk\Otpravkapochtaru\Joomla;

defined('_JEXEC') or die;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Joomla HTTP may return PSR-7 streams with the cursor already at EOF.
 */
final class RewindingPsr18Client implements ClientInterface
{
    public function __construct(private readonly ClientInterface $client)
    {
    }

    /**
     * @throws ClientExceptionInterface
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
