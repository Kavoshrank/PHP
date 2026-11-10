<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use KavoshRank\Webhook\Receiver;
use KavoshRank\Webhook\WebhookEvent;
use KavoshRank\Exceptions\AuthenticationException;

$receiver = new Receiver('YOUR_SITE_SECRET_TOKEN');

// Register handlers for incoming KavoshRank master engine actions
$receiver->on('publish_article', function (WebhookEvent $event) {
    $title = $event->get('title');
    $content = $event->get('content');
    $category = $event->get('category');

    // Save or publish article in your custom CMS / database here
    return [
        'published' => true,
        'slug' => $event->get('slug', 'sample-article'),
    ];
});

$receiver->on('audit', function (WebhookEvent $event) {
    // Return site audit metrics
    return [
        'php_version' => PHP_VERSION,
        'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'custom',
    ];
});

try {
    $response = $receiver->handleRequest();
    header('Content-Type: application/json');
    echo json_encode($response);
} catch (AuthenticationException $e) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
} catch (\Exception $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
