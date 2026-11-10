<?php

declare(strict_types=1);

namespace KavoshRank\Http;

interface HttpClientInterface
{
    /**
     * Send an HTTP request and return a Response.
     *
     * @param string $method GET, POST, PUT, DELETE
     * @param string $url Absolute URL
     * @param array<string, string> $headers Key-value header map
     * @param string|null $body Request payload body
     * @param int $timeout Timeout in seconds
     * @return Response
     * @throws \KavoshRank\Exceptions\KavoshRankException
     */
    public function request(string $method, string $url, array $headers = [], ?string $body = null, int $timeout = 30): Response;
}
