<?php

/**
 * Factory for Russian Post SOAP tracking clients and SOAP credentials.
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

use Webtolk\Otpravkapochtaru\Configuration\CredentialsProvider;

final class SoapRequest
{
    private const TRACKING_BASE_WSDL = 'https://tracking.russianpost.ru';
    private const SERVICE_SINGLE     = 'rtm34_wsdl.xml';
    private const SERVICE_PACK       = 'fc_wsdl.xml';

    /**
     * Store the credential provider used by both SOAP services.
     *
     * @param   CredentialsProvider  $credentialsProvider  Credentials source for SOAP clients.
     *
     * @since   3.0.0
     */
    public function __construct(private readonly CredentialsProvider $credentialsProvider)
    {
    }

    /**
     * Create a SOAP 1.2 client for single RPO history and NPay requests.
     *
     * @return  \SoapClient
     *
     * @since   3.0.0
     *
     * @throws  \SoapFault
     */
    public function createSingleClient(): \SoapClient
    {
        return $this->createClient(self::SERVICE_SINGLE, SOAP_1_2);
    }

    /**
     * Create a SOAP 1.1 client for batch ticket requests.
     *
     * @return  \SoapClient
     *
     * @since   3.0.0
     *
     * @throws  \SoapFault
     */
    public function createPackClient(): \SoapClient
    {
        return $this->createClient(self::SERVICE_PACK, SOAP_1_1);
    }

    /**
     * Return tracking SOAP login from plugin/library parameters.
     *
     * @return  string
     *
     * @since   3.0.0
     */
    public function getTrackingLogin(): string
    {
        return $this->credentialsProvider->getTrackingLogin();
    }

    /**
     * Return tracking SOAP password from plugin/library parameters.
     *
     * @return  string
     *
     * @since   3.0.0
     */
    public function getTrackingPassword(): string
    {
        return $this->credentialsProvider->getTrackingPassword();
    }

    /**
     * Instantiate a SOAP client with project timeout and the SOAP mode required by the target service.
     *
     * @param   string  $serviceWSDL  SOAP service WSDL file name.
     * @param   int     $soapVersion  SOAP protocol version constant.
     *
     * @return  \SoapClient
     *
     * @since   3.0.0
     *
     * @throws  \SoapFault
     */
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
