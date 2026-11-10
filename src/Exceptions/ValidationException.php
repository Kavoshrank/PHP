<?php

declare(strict_types=1);

namespace KavoshRank\Exceptions;

class ValidationException extends KavoshRankException
{
    protected array $errors = [];

    public function __construct(string $message = '', int $statusCode = 400, ?array $responseBody = null, array $errors = [])
    {
        parent::__construct($message, $statusCode, $responseBody);
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
