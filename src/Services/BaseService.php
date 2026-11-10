<?php

declare(strict_types=1);

namespace KavoshRank\Services;

use KavoshRank\Config;
use KavoshRank\Exceptions\AuthenticationException;
use KavoshRank\Exceptions\KavoshRankException;
use KavoshRank\Exceptions\RateLimitException;
use KavoshRank\Exceptions\ServerException;
use KavoshRank\Exceptions\ValidationException;
use KavoshRank\Http\HttpClientInterface;
use KavoshRank\Http\Response;

abstract class BaseService
{
    protected Config $config;
    protected HttpClientInterface $httpClient;

    public function __construct(Config $config, HttpClientInterface $httpClient)
    {
        $this->config = $config;
        $this->httpClient = $httpClient;
    }

    /**
     * Executes an API request and handles status codes and errors uniformly.
     */
    protected function request(string $method, string $path, array $queryParams = [], ?array $body = null): array
    {
        $url = $this->config->getBaseUrl() . '/' . ltrim($path, '/');

        if (!empty($queryParams)) {
            $url .= (str_contains($url, '?') ? '&' : '?') . http_build_query($queryParams);
        }

        $headers = array_merge(
            ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
            $this->config->getAuthHeaders()
        );

        $jsonBody = $body !== null ? json_encode($body) : null;
        $response = $this->httpClient->request($method, $url, $headers, $jsonBody, $this->config->getTimeout());

        return $this->handleResponse($response);
    }

    protected function handleResponse(Response $response): array
    {
        $statusCode = $response->getStatusCode();
        $data = $response->json() ?? [];

        if ($response->isSuccess()) {
            return $data;
        }

        $message = $data['message'] ?? $data['error'] ?? "API error returned with status {$statusCode}";

        if ($statusCode === 401 || $statusCode === 403) {
            throw new AuthenticationException($message, $statusCode, $data);
        }

        if ($statusCode === 400 || $statusCode === 422) {
            $errors = $data['errors'] ?? [];
            throw new ValidationException($message, $statusCode, $data, is_array($errors) ? $errors : []);
        }

        if ($statusCode === 429) {
            $retryAfterHeader = $response->getHeader('Retry-After');
            $retryAfter = $retryAfterHeader !== null ? (int) $retryAfterHeader : null;
            throw new RateLimitException($message, $statusCode, $data, $retryAfter);
        }

        if ($statusCode >= 500) {
            throw new ServerException($message, $statusCode, $data);
        }

        throw new KavoshRankException($message, $statusCode, $data);
    }
}
