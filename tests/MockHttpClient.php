<?php

declare(strict_types=1);

namespace KavoshRank\Tests;

use KavoshRank\Http\HttpClientInterface;
use KavoshRank\Http\Response;

class MockHttpClient implements HttpClientInterface
{
    public array $requests = [];
    public ?Response $nextResponse = null;

    public function request(string $method, string $url, array $headers = [], ?string $body = null, int $timeout = 30): Response
    {
        $this->requests[] = [
            'method' => $method,
            'url' => $url,
            'headers' => $headers,
            'body' => $body,
            'timeout' => $timeout,
        ];

        return $this->nextResponse ?? new Response(200, ['Content-Type' => 'application/json'], '{"success":true}');
    }
}
