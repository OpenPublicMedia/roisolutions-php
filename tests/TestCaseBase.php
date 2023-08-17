<?php


namespace OpenPublicMedia\RoiSolutions\Test;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use OpenPublicMedia\RoiSolutions\Rest\Client;
use PHPUnit\Framework\TestCase;
use Tochka\Cache\ArrayFileCache;

class TestCaseBase extends TestCase
{
    protected Client $restClient;
    protected Client $restClientWithCache;
    protected MockHandler $mockHandler;

    /**
     * Create client with mock handler.
     */
    protected function setUp(): void
    {
        $this->mockHandler = new MockHandler();
        $this->restClient = new Client(
            'USER_ID',
            'PASSWORD',
            'CLIENT_CODE',
            httpClientOptions: ['handler' => $this->mockHandler]
        );
        $cache = new ArrayFileCache(__DIR__ . '/data', 'test.db');
        $this->restClientWithCache = new Client(
            'USER_ID',
            'PASSWORD',
            'CLIENT_CODE',
            httpClientOptions: ['handler' => $this->mockHandler],
            cache: $cache
        );
    }

    /**
     * Returns a regular JSON response.
     */
    protected static function jsonFixtureResponse(string $name, int $statusCode = 200): Response
    {
        return self::apiJsonResponse($statusCode, file_get_contents(__DIR__ . "/fixtures/$name.json"));
    }

    /**
     * Returns a response with a provided code and json content.
     */
    protected static function apiJsonResponse(int $code, string $json = '[]'): Response
    {
        return new Response($code, ['Content-Type' => 'application/json'], $json);
    }

    /**
     * Returns a response with common error properties.
     */
    protected static function apiErrorResponse(
        int $code = 400,
        string $title = 'Error title',
        string $detail = 'Error detail',
        string $instanceCode = "ABCD:1234",
        string $helpLink = "https://foo.com/api/help/index.html",
        ?int $statusCode = null
    ): Response {
        return new Response($code, ['Content-Type' => 'application/json'], json_encode([
            'statusCode' => $statusCode ?? $code,
            'title' => $title,
            'detail' => $detail,
            'instanceCode' => $instanceCode,
            'helpLink' => $helpLink,
        ]));
    }
}
