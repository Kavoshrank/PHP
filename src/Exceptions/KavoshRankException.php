<?php

declare(strict_types=1);

namespace KavoshRank\Exceptions;

use Exception;

class KavoshRankException extends Exception
{
    protected ?int $statusCode = null;
    protected ?array $responseBody = null;

    public function __construct(string $message = '', int $statusCode = 0, ?array $responseBody = null, ?Exception $previous = null)
    {
        parent::__construct($message, $statusCode, $previous);
        $this->statusCode = $statusCode > 0 ? $statusCode : null;
        $this->responseBody = $responseBody;
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    public function getResponseBody(): ?array
    {
        return $this->responseBody;
    }
}
