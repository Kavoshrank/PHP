<?php

declare(strict_types=1);

namespace KavoshRank;

class Config
{
    public const DEFAULT_BASE_URL = 'https://backend.kavoshrank.ir';
    public const DEFAULT_TIMEOUT = 30;

    private string $baseUrl;
    private ?string $siteToken = null;
    private ?string $apiKey = null;
    private ?int $projectId = null;
    private int $timeout;
    private array $customHeaders = [];

    public function __construct(array $options = [])
    {
        $this->baseUrl = rtrim((string) ($options['base_url'] ?? self::DEFAULT_BASE_URL), '/');
        $this->siteToken = isset($options['site_token']) ? trim((string) $options['site_token']) : null;
        $this->apiKey = isset($options['api_key']) ? trim((string) $options['api_key']) : null;
        $this->projectId = isset($options['project_id']) ? (int) $options['project_id'] : null;
        $this->timeout = (int) ($options['timeout'] ?? self::DEFAULT_TIMEOUT);
        $this->customHeaders = (array) ($options['headers'] ?? []);
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getSiteToken(): ?string
    {
        return $this->siteToken;
    }

    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    public function getProjectId(): ?int
    {
        return $this->projectId;
    }

    public function getTimeout(): int
    {
        return $this->timeout;
    }

    public function getCustomHeaders(): array
    {
        return $this->customHeaders;
    }

    public function getAuthHeaders(): array
    {
        $headers = [];

        if (!empty($this->siteToken)) {
            $headers['X-KavoshRank-Secret-Token'] = $this->siteToken;
            $headers['X-TSW-Secret-Token'] = $this->siteToken;
        }

        if (!empty($this->apiKey)) {
            $headers['Authorization'] = 'Bearer ' . $this->apiKey;
        }

        return array_merge($headers, $this->customHeaders);
    }
}
