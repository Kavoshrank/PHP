<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use KavoshRank\Client;
use KavoshRank\Exceptions\AuthenticationException;
use KavoshRank\Exceptions\ValidationException;

$client = new Client([
    'site_token' => 'YOUR_SITE_SECRET_TOKEN',
    'base_url' => 'https://backend.kavoshrank.ir',
]);

try {
    // 1. Generate an AI SEO article
    $article = $client->articles->generate([
        'topic' => 'Top 10 Modern SEO Strategies for 2026',
        'word_count' => 1200,
        'use_serp' => true,
    ]);
    echo "Article generation response:\n";
    print_r($article);

    // 2. Generate an audio podcast from an article
    $audio = $client->audio->generate([
        'post_id' => 101,
        'title' => 'Top 10 Modern SEO Strategies for 2026',
        'content' => 'Full article narration script...',
        'voice' => 'Puck',
        'theme' => 'glassmorphic',
    ]);
    echo "Audio podcast generated:\n";
    print_r($audio);

    // 3. Consult AI Copilot
    $copilot = $client->copilot->consult([
        'question' => 'Which product is best for beginners?',
        'current_product' => [
            'id' => 50,
            'title' => 'Professional Camera Kit',
            'price' => 1200,
        ],
    ]);
    echo "Copilot response:\n";
    print_r($copilot);

} catch (ValidationException $e) {
    echo "Validation error: " . $e->getMessage() . "\n";
    print_r($e->getErrors());
} catch (AuthenticationException $e) {
    echo "Authentication error: " . $e->getMessage() . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
