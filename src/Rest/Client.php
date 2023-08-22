<?php
declare(strict_types=1);


namespace OpenPublicMedia\RoiSolutions\Rest;

use DateTime;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use OpenPublicMedia\RoiSolutions\Rest\Exception\RequestException;
use OpenPublicMedia\RoiSolutions\Rest\Resource\Donor;
use OpenPublicMedia\RoiSolutions\Rest\Resource\DonorEmailAddress;
use OpenPublicMedia\RoiSolutions\Rest\PagedResults\DonorSearchPagedResults;
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
     * @url https://secure2.roisolutions.net/api/help/#/system/post-logon
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
    public function getUtcTime(): DateTime
    {
        $response = $this->get('time');
        return new DateTime($response->utc_datetime);
    }

    /**
     * Gets the endpoint date and time in the endpoint timezone.
     *
     * @url https://secure2.roisolutions.net/api/help/#/system/get-time
     */
    public function getSystemTime(): DateTime
    {
        $response = $this->get('time');
        return new DateTime($response->roi_system_datetime);
    }

    /**
     * Gets a single donor record based on a ROI Family ID.
     *
     * @url https://secure2.roisolutions.net/api/help/#/donors/get-donor
     */
    public function getDonor(string $roiFamilyId): Donor
    {
        return Donor::fromJson($this->get("donors/$roiFamilyId"));
    }

    /**
     * Searches donor records with provided criteria.
     *
     * @url https://secure2.roisolutions.net/api/help/#/donors/get-donors
     */
    public function searchDonors(
        ?int $page = null,
        ?int $limit = null,
        ?string $email = null,
        ?string $nameFirst = null,
        ?string $nameLast = null,
        ?string $street = null,
        ?string $city = null,
        ?string $state = null,
        ?string $postalCode = null,
        ?string $phone = null,
        ?string $externalId = null,
        ?string $externalIdType = null
    ): DonorSearchPagedResults {
        if (($externalId && !$externalIdType)
            || ($externalIdType && !$externalId)
        ) {
            throw new \RuntimeException("Both externalId and externalIdType must be set.");
        }
        $query = [];
        $query['email'] = $email;
        $query['name-first'] = $nameFirst;
        $query['name-last'] = $nameLast;
        $query['street'] = $street;
        $query['city'] = $city;
        $query['state'] = $state;
        $query['postal-code'] = $postalCode;
        $query['phone'] = $phone;
        $query['external-id'] = $externalId;
        $query['external-id-type'] = $externalIdType;
        if (empty(array_filter($query))) {
            throw new \RuntimeException("At least one search query parameter must be provided.");
        }
        $query['page'] = $page;
        $query['limit'] = $limit;
        return new DonorSearchPagedResults($this, 'donors', $query);
    }

    /**
     * Adds a new donor.
     *
     * @url https://secure2.roisolutions.net/api/help/#/BETA%20TESTING/post-donor
     */
    public function addDonor(
        string $originationVendor,
        string $nameLast,
        ?string $nameFirst = null,
        ?string $nameMiddle = null,
        ?string $namePrefixCode = null,
        ?string $nameSuffix = null,
        ?bool $doNotContact = null
    ): Donor {
        return Donor::fromJson($this->post('donors', ['json' => [
            'origination_vendor' => $originationVendor,
            'name_last' => $nameLast,
            'name_first' => $nameFirst,
            'name_middle' => $nameMiddle,
            // Name prefix codes do not appear to work (tested 18 Aug 2023).
            'name_prefix_code' => $namePrefixCode,
            'name_suffix' => $nameSuffix,
            'do_not_contact' => $doNotContact ? (string) $doNotContact : null,
        ]]));
    }

    /**
     * Adds a new email address to an existing donor.
     */
    public function addDonorEmailAddress(
        string $roiFamilyId,
        string $originationVendor,
        string $emailAddress,
        ?string $typeCode = null,
        ?DateTime $verificationDate = null,
        ?bool $emailBounced = null,
    ): DonorEmailAddress {
        $json = [
            'origination_vendor' => $originationVendor,
            'email_address' => $emailAddress,
            'email_type_code' => $typeCode,
            'verification_date' => $verificationDate?->format('Y-m-d\TH:i:s.000p') ?? null,
            'email_bounced' => $emailBounced === true ? 'Y' : ($emailBounced === false ? 'N' : null)
        ];
        return DonorEmailAddress::fromJson($this->post("donors/$roiFamilyId/emails", ['json' => array_filter($json)]));
    }
}
