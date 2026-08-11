<?php

/**
 * Shared Joomla transport factory for LapayGroup Russian Post PSR-18 transport.
 *
 * @package     WT Otpravkapochtaru
 * @version     3.0.0
 * @author      Sergey Tolkachyov
 * @copyright   Copyright (c) 2026 Sergey Tolkachyov, WebTolk. All rights reserved.
 * @license     GNU/GPL 3.0
 */

namespace Webtolk\Otpravkapochtaru\Joomla;

defined('_JEXEC') or die;

use Joomla\Http\HttpFactory;
use Laminas\Diactoros\RequestFactory;
use Laminas\Diactoros\StreamFactory;
use Laminas\Diactoros\UploadedFileFactory;
use LapayGroup\RussianPost\Http\Psr18Transport;

/**
 * Helper for building upstream PSR-18 transport from Joomla HTTP layer.
 */
final class Psr18TransportFactory
{
    /**
     * Build PSR-18 transport with Joomla HTTP client and Laminas factories.
     *
     * @param int $timeout Request timeout in seconds.
     */
    public static function create(int $timeout = 60): Psr18Transport
    {
        $client = (new HttpFactory())->getHttp(
            ['timeout' => $timeout],
            ['curl', 'stream']
        );

        return new Psr18Transport(
            new RewindingPsr18Client($client),
            new RequestFactory(),
            new StreamFactory(),
            new UploadedFileFactory(),
        );
    }
}
