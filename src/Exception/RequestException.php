<?php

namespace OpenPublicMedia\RoiSolutions\Exception;

use Psr\Http\Message\ResponseInterface;
use RuntimeException;

/**
 * ROI Solutions REST API general request exception.
 */
class RequestException extends RuntimeException
{

    public function __construct(
        private readonly int $statusCodeReported,
        private readonly string $title,
        private readonly string $detail,
        private readonly string $instanceCode,
        private readonly string $helpLink
    ) {
        parent::__construct($this->title, $this->statusCodeReported);
    }

    public static function fromResponse(ResponseInterface $response): RequestException
    {
        $details = json_decode($response->getBody()->getContents());
        // The reported status code and actual response status code do not
        // always match. Create a specific exception for ket status codes when
        // possible.
        $exception = match ($details->statusCode) {
            401 => AccessDeniedException::class,
            404 => NotFoundException::class,
            429 => TooManyRequestsException::class,
            default => RequestException::class,
        };
        return new $exception(
            $details->statusCode,
            $details->title,
            $details->detail,
            $details->instanceCode,
            $details->helpLink,
        );
    }

    public function getStatusCodeReported(): int
    {
        return $this->statusCodeReported;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDetail(): string
    {
        return $this->detail;
    }

    public function getInstanceCode(): string
    {
        return $this->instanceCode;
    }

    public function getHelpLink(): string
    {
        return $this->helpLink;
    }
}
