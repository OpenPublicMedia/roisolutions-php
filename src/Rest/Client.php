<?php
declare(strict_types=1);


namespace OpenPublicMedia\RoiSolutions\Rest;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use OpenPublicMedia\RoiSolutions\Exception\AccessDeniedException;
use OpenPublicMedia\RoiSolutions\Exception\NotFoundException;
use OpenPublicMedia\RoiSolutions\Exception\RequestException;
use OpenPublicMedia\RoiSolutions\Exception\TooManyRequestsException;
use Psr\Http\Message\ResponseInterface;

/**
 * ROI Solutions REST API Client.
 *
 * @url https://secure2.roisolutions.net/api/help
 *
 * @package OpenPublicMedia\RoiSolutions\Rest
 */
class Client
{
    const SESSION_EXPIRE_KEY = 'open_public_media.roi.rest.session_expire';
    const SESSION_TOKEN_KEY = 'open_public_media.roi.rest.session_token';

    protected ?GuzzleClient $client;
    private ?string $token = null;


    /**
     * Client constructor.
     *
     * @param array<string, mixed> $httpClientOptions
     * @param ?object $cache
     *   Cache interface for storing an auth token. A PSR-16 compliant
     *   interface is preferred but any class providing `set($key, $value)` and
     *   `get($key, $default)` methods will suffice.
     */
    public function __construct(
        private readonly string $userId,
        private readonly string $password,
        protected readonly string $clientCode,
        protected string $baseUri = 'https://secure2.roisolutions.net/api/1.0/',
        array $httpClientOptions = [],
        protected ?object $cache = null
    ) {
        $this->client = new GuzzleClient([
            'base_uri' => $baseUri,
            'http_errors' => false,
        ] + $httpClientOptions);
    }

    /**
     * Gets an API token, refreshing it if necessary.
     *
     * @url https://www.engagingnetworks.support/api/rest/#/operations/authenticate
     */
    private function getToken(): ?string
    {
        if ($this->token) {
            $token = $this->token;
        } else {
            try {
                $token = $this->cache?->get(self::SESSION_TOKEN_KEY);
            } catch (\Exception $e) {
                throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
            }
        }

        try {
            $expires = $this->cache?->get(self::SESSION_EXPIRE_KEY, 0);
            if (time() >= $expires) {
                $token = null;
            }
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
        }

        if (!$token) {
            $response = $this->request('post', 'logon', [
                'json' => [
                    'userid' => $this->userId,
                    'password' => $this->password,
                    'clientcode' => $this->clientCode,
                ]
            ]);
            $data = json_decode($response->getBody()->getContents());
            $token = $data->token;

            if (is_callable([$this->cache, 'set'])) {
                $this->cache->set(self::SESSION_TOKEN_KEY, $token);
                // The token is valid during the current calendar day, based on the
                // endpoint's time zone.
                $expires = $this->getSystemTime();
                $expires->setTime(24, 0);
                $this->cache->set(self::SESSION_EXPIRE_KEY, $expires->getTimestamp());
            }
        }

        if ($token != $this->token) {
            $this->token = $token;
        }

        return $this->token;
    }

    /**
     * Sends a request to the API.
     *
     * @param array<string, mixed> $options
     */
    public function request(string $method, string $endpoint, array $options = []): ResponseInterface
    {
        // Add Authorization header for protected endpoints.
        if ($endpoint != 'logon' && $endpoint != 'ping' && $endpoint != 'time') {
            $options['headers'] = ['Authorization' => "Bearer {$this->getToken()}"] + ($options['headers'] ?? []);
        }

        try {
            $response = $this->client->request($method, $endpoint, $options);
        } catch (GuzzleException $e) {
            throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
        }

        if ($response->getStatusCode() >= 400) {
            throw RequestException::fromResponse($response);
        }

        return $response;
    }

    /**
     * Sends a GET request to the API and parses a JSON response.
     *
     * @param array<string, mixed> $options
     */
    public function get(string $endpoint, array $options = []): object
    {
        $response = $this->request('get', $endpoint, $options);
        return json_decode($response->getBody()->getContents());
    }

    /**
     * Sends a POST request to the API and parses a JSON response.
     *
     * @param array<string, mixed> $options
     */
    public function post(string $endpoint, array $options = []): object
    {
        $response = $this->request('post', $endpoint, $options);
        return json_decode($response->getBody()->getContents());
    }

    /**
     * Sends a "ping" to the API endpoint.
     *
     * @url https://secure2.roisolutions.net/api/help/#/system/get-ping
     *
     * @return string
     *   "pong!" if the API is up and healthy.
     */
    public function ping(): string
    {
        $response = $this->request('get', 'ping');
        return $response->getBody()->getContents();
    }

    /**
     * Gets the endpoint date and time in UTC.
     *
     * @url https://secure2.roisolutions.net/api/help/#/system/get-time
     */
    public function getUtcTime(): \DateTime
    {
        $response = $this->get('time');
        return new \DateTime($response->utc_datetime);
    }

    /**
     * Gets the endpoint date and time in the endpoint timezone.
     *
     * @url https://secure2.roisolutions.net/api/help/#/system/get-time
     */
    public function getSystemTime(): \DateTime
    {
        $response = $this->get('time');
        return new \DateTime($response->roi_system_datetime);
    }
}