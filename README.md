# ROI Solutions PHP Library

This library abstracts interactions with ROI Solutions APIs.

## Implemented APIs:

- [REST API](https://secure2.roisolutions.net/api/help/)

## Installation

Install via composer:

```bash
composer require openpublicmedia/roisolutions-php
```

## Use

### REST API

The `OpenPublicMedia\RoiSolutions\Rest\Client` queries the REST API.

### Examples

#### Creating a client

```php
use OpenPublicMedia\RoiSolutions\Rest\Client;

$userId = 'USER_ID';
$password = 'pAsSw0rD';
$clientCode = 'CLIENTCODE';

$client = new Client($userId, $password, $clientCode);
```

Providing a cache service is also supported (and recommended) when creating the
client. If the client has a cache service it will be used to cache the
authentication token provided by the API across multiple requests for the
lifetime of the token.

A PSR-16 compliant interface is preferred but any class providing
`set($key, $value)` and `get($key, $default)` methods will suffice.

```php
use OpenPublicMedia\RoiSolutions\Rest\Client;
use Tochka\Cache\ArrayFileCache;

$userId = 'USER_ID';
$password = 'pAsSw0rD';
$clientCode = 'CLIENTCODE';
$cache = new ArrayFileCache('.', 'my_awesome_cache');

$client = new Client($userId, $password, $clientCode, cache: $cache);
```

#### Handling exceptions

Custom exceptions are provided for 404 response and general errors. Additional
information from the API response is captured in these exceptions.

```php
use \OpenPublicMedia\RoiSolutions\Exception\RequestException;

try {
    $results = $client->request('get', 'donors');
} catch (RequestException $e) {
    var_dump(get_class($e));
    var_dump($e->getMessage());
    var_dump($e->getCode());
    var_dump($e->getStatusCodeReported());
    var_dump($e->getTitle());
    var_dump($e->getDetail());
    var_dump($e->getInstanceCode());
    var_dump($e->getHelpLink());
}
```

## Development goals

See [CONTRIBUTING](CONTRIBUTING.md) for information about contributing to
this project.

### v1

- [x] REST API client (`\OpenPublicMedia\RoiSolutions\Rest\Client`)
- [x] API direct querying (`$client->request()`)
- [x] Result/error handling
- [x] System
  - [x] [Logon](https://secure2.roisolutions.net/api/help/#/system/post-logon)
  - [x] [Ping](https://secure2.roisolutions.net/api/help/#/system/get-ping)
  - [x] [Get time](https://secure2.roisolutions.net/api/help/#/system/get-time)
- [ ] Donors
  - [ ] [Search donors](https://secure2.roisolutions.net/api/help/#/donors/get-donors)
  - [x] [Get donor](https://secure2.roisolutions.net/api/help/#/donors/get-donor)
    - [ ] Support for advanced query parameters (`include`, `summary.*`, `donations.*`)
  - [ ] [Create donor](https://secure2.roisolutions.net/api/help/#/BETA%20TESTING/post-donor) (BETA)
  - [ ] [Update donor](https://secure2.roisolutions.net/api/help/#/BETA%20TESTING/patch-donors) (BETA)
