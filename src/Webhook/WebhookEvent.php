<?php

declare(strict_types=1);

namespace KavoshRank\Webhook;

class WebhookEvent
{
    private string $action;
    private array $data;
    private array $headers;

    public function __construct(string $action, array $data, array $headers = [])
    {
        $this->action = $action;
        $this->data = $data;
        $this->headers = $headers;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }
}
