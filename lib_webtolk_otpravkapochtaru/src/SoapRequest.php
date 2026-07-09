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

use Webtolk\Otpravkapochtaru\Configuration\CredentialsProvider;

final class SoapRequest
{
    private const TRACKING_BASE_WSDL = 'https://tracking.russianpost.ru';
    private const SERVICE_SINGLE     = 'rtm34_wsdl.xml';
    private const SERVICE_PACK       = 'fc_wsdl.xml';

    public function __construct(private readonly CredentialsProvider $credentialsProvider)
    {
    }

    public function createSingleClient(): \SoapClient
    {
        return $this->createClient(self::SERVICE_SINGLE, SOAP_1_2);
    }

    public function createPackClient(): \SoapClient
    {
        return $this->createClient(self::SERVICE_PACK, SOAP_1_1);
    }

    public function getTrackingLogin(): string
    {
        return $this->credentialsProvider->getTrackingLogin();
    }

    public function getTrackingPassword(): string
    {
        return $this->credentialsProvider->getTrackingPassword();
    }

    private function createClient(string $serviceWSDL, int $soapVersion): \SoapClient
    {
        return new \SoapClient(
            self::TRACKING_BASE_WSDL . '/tracking-web-static/' . $serviceWSDL,
            [
                'trace'              => 1,
                'soap_version'       => $soapVersion,
                'use'                => SOAP_LITERAL,
                'style'              => SOAP_DOCUMENT,
                'connection_timeout' => $this->credentialsProvider->getHttpTimeout(),
            ]
        );
    }
}
