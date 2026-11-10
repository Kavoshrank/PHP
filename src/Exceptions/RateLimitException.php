<?php

declare(strict_types=1);

namespace KavoshRank\Exceptions;

class RateLimitException extends KavoshRankException
{
    protected ?int $retryAfter = null;

    public function __construct(string $message = 'Rate limit exceeded', int $statusCode = 429, ?array $responseBody = null, ?int $retryAfter = null)
    {
        parent::__construct($message, $statusCode, $responseBody);
        $this->retryAfter = $retryAfter;
    }

    public function getRetryAfter(): ?int
    {
        return $this->retryAfter;
    }
}
