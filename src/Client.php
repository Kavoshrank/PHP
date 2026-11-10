<?php

declare(strict_types=1);

namespace KavoshRank;

use KavoshRank\Http\CurlHttpClient;
use KavoshRank\Http\HttpClientInterface;
use KavoshRank\Services\ArticleService;
use KavoshRank\Services\AudioService;
use KavoshRank\Services\CopilotService;
use KavoshRank\Services\EventService;
use KavoshRank\Services\HealthService;
use KavoshRank\Services\ReviewService;
use KavoshRank\Webhook\Receiver;

class Client
{
    public const VERSION = '1.0.0';

    private Config $config;
    private HttpClientInterface $httpClient;

    public readonly ArticleService $articles;
    public readonly AudioService $audio;
    public readonly CopilotService $copilot;
    public readonly ReviewService $reviews;
    public readonly EventService $events;
    public readonly HealthService $health;

    /**
     * @param array{
     *   site_token?: string,
     *   api_key?: string,
     *   project_id?: int,
     *   base_url?: string,
     *   timeout?: int,
     *   headers?: array<string, string>,
     *   http_client?: HttpClientInterface
     * } $options
     */
    public function __construct(array $options = [])
    {
        $this->config = new Config($options);
        $this->httpClient = $options['http_client'] ?? new CurlHttpClient();

        $this->articles = new ArticleService($this->config, $this->httpClient);
        $this->audio = new AudioService($this->config, $this->httpClient);
        $this->copilot = new CopilotService($this->config, $this->httpClient);
        $this->reviews = new ReviewService($this->config, $this->httpClient);
        $this->events = new EventService($this->config, $this->httpClient);
        $this->health = new HealthService($this->config, $this->httpClient);
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    public function getHttpClient(): HttpClientInterface
    {
        return $this->httpClient;
    }

    /**
     * Helper to create an incoming webhook receiver using the configured site token.
     */
    public function createWebhookReceiver(?string $token = null): Receiver
    {
        $secretToken = $token ?? $this->config->getSiteToken() ?? '';
        return new Receiver($secretToken);
    }
}
