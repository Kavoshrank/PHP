<?php

declare(strict_types=1);

namespace KavoshRank\Http;

class Response
{
    private int $statusCode;
    private array $headers;
    private string $body;
    private ?array $decodedJson = null;

    public function __construct(int $statusCode, array $headers, string $body)
    {
        $this->statusCode = $statusCode;
        $this->headers = $headers;
        $this->body = $body;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getHeader(string $name): ?string
    {
        $normalized = strtolower($name);
        foreach ($this->headers as $key => $value) {
            if (strtolower((string) $key) === $normalized) {
                return is_array($value) ? implode(', ', $value) : (string) $value;
            }
        }
        return null;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function json(): ?array
    {
        if ($this->decodedJson === null && !empty($this->body)) {
            $decoded = json_decode($this->body, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $this->decodedJson = $decoded;
            }
        }
        return $this->decodedJson;
    }

    public function isSuccess(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }
}
