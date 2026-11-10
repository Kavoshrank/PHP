<?php

declare(strict_types=1);

namespace KavoshRank\Webhook;

use KavoshRank\Exceptions\AuthenticationException;

class Receiver
{
    private string $secretToken;
    /** @var array<string, callable> */
    private array $handlers = [];

    public function __construct(string $secretToken)
    {
        $this->secretToken = trim($secretToken);
    }

    /**
     * Register a callback for a specific incoming action.
     *
     * @param string $action Action name (e.g. 'publish_article', 'sync_categories', 'audit', 'ping')
     * @param callable $callback function(WebhookEvent $event): mixed
     */
    public function on(string $action, callable $callback): self
    {
        $this->handlers[$action] = $callback;
        return $this;
    }

    /**
     * Verifies that the provided token matches the configured secret token securely.
     */
    public function verifyToken(?string $incomingToken): bool
    {
        if (empty($incomingToken) || empty($this->secretToken)) {
            return false;
        }

        return hash_equals($this->secretToken, trim($incomingToken));
    }

    /**
     * Extracts token from standard server request headers.
     */
    public function extractTokenFromHeaders(array $headers): ?string
    {
        $candidates = [
            'x-kavoshrank-secret-token',
            'x-tsw-secret-token',
            'http_x_kavoshrank_secret_token',
            'http_x_tsw_secret_token',
        ];

        foreach ($headers as $key => $val) {
            $normalized = strtolower(str_replace('-', '_', (string) $key));
            if (in_array($normalized, $candidates, true)) {
                return is_array($val) ? (string) ($val[0] ?? '') : (string) $val;
            }
        }

        // Check Authorization header for Bearer
        $authHeader = $headers['authorization'] ?? $headers['http_authorization'] ?? $headers['Authorization'] ?? null;
        if (!empty($authHeader) && preg_match('/Bearer\s+(\S+)/i', (string) $authHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Handle an incoming HTTP request.
     *
     * @param array|null $server $_SERVER or headers array
     * @param string|null $rawBody php://input string or JSON body
     * @return array Standard response array
     * @throws AuthenticationException
     */
    public function handleRequest(?array $server = null, ?string $rawBody = null): array
    {
        $server = $server ?? $_SERVER;
        $rawBody = $rawBody ?? (string) file_get_contents('php://input');

        $token = $this->extractTokenFromHeaders($server);

        if (!$this->verifyToken($token)) {
            throw new AuthenticationException('Unauthorized: invalid or missing KavoshRank secret token', 401);
        }

        $payload = [];
        if (!empty($rawBody)) {
            $decoded = json_decode($rawBody, true);
            if (is_array($decoded)) {
                $payload = $decoded;
            }
        }

        $action = (string) ($payload['action'] ?? $payload['event'] ?? 'ping');
        $event = new WebhookEvent($action, $payload, $server);

        if (isset($this->handlers[$action])) {
            $result = ($this->handlers[$action])($event);
            return [
                'success' => true,
                'action' => $action,
                'result' => $result,
            ];
        }

        if ($action === 'ping') {
            return [
                'success' => true,
                'action' => 'ping',
                'message' => 'pong',
            ];
        }

        return [
            'success' => true,
            'action' => $action,
            'message' => 'No specific handler registered for action',
        ];
    }
}
