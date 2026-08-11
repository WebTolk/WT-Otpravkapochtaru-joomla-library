<?php

namespace Webtolk\Tests\Unit\Joomla;

use Laminas\Diactoros\Request;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\Stream;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Webtolk\Otpravkapochtaru\Joomla\RewindingPsr18Client;

final class RewindingPsr18ClientTest extends TestCase
{
    public function testSendRequestRewindsSeekableResponseBody(): void
    {
        $stream = fopen('php://temp', 'r+');
        self::assertIsResource($stream);

        fwrite($stream, '{"ok":true}');

        $response = new Response(new Stream($stream));
        $client   = new RewindingPsr18Client(
            new class ($response) implements ClientInterface {
                public function __construct(private readonly ResponseInterface $response)
                {
                }

                public function sendRequest(RequestInterface $request): ResponseInterface
                {
                    return $this->response;
                }
            }
        );

        $rewoundResponse = $client->sendRequest(new Request('https://example.test'));
        $body            = $rewoundResponse->getBody();

        self::assertSame(0, $body->tell());
        self::assertSame('{"ok":true}', $body->getContents());
    }
}
