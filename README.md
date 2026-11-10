# KavoshRank PHP SDK

Official PHP SDK for integrating with the KavoshRank AI SEO, Content Automation, Audio Studio, and Storefront Copilot Master Engine.

## Requirements

- PHP >= 8.1
- cURL extension (`ext-curl`)
- JSON extension (`ext-json`)

## Installation

Add to your project's `composer.json`:

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/Kavoshrank/PHP.git"
    }
  ],
  "require": {
    "kavoshrank/php": "dev-main"
  }
}
```

Or install via Composer once published:

```bash
composer require kavoshrank/php
```

## Quickstart

Initialize the `KavoshRank\Client`:

```php
use KavoshRank\Client;

$client = new Client([
    'site_token' => 'YOUR_SITE_SECRET_TOKEN', // or 'api_key' => 'YOUR_JWT_TOKEN'
    'base_url'   => 'https://backend.kavoshrank.ir',
    'project_id' => 10, // optional default project context
]);
```

### 1. AI SEO Articles

```php
// Generate an article
$article = $client->articles->generate([
    'topic'      => 'Advanced Microservice Architectures in 2026',
    'word_count' => 1500,
    'use_serp'   => true,
]);

// Suggest titles based on keyword
$titles = $client->articles->suggestTitles('microservices');

// Query Google suggestions
$suggestions = $client->articles->googleSuggest('microservices');

// Check job status for asynchronous tasks
$job = $client->articles->getJob('job_123456');

// Publish article
$client->articles->publish($article['id']);
```

### 2. Audio Studio & Podcasts

```php
// Generate audio narration from article text
$audio = $client->audio->generate([
    'post_id' => 120,
    'title'   => 'Microservices in 2026',
    'content' => 'Full narration text for the podcast episode...',
    'voice'   => 'Puck',
    'theme'   => 'glassmorphic', // glassmorphic, minimal, waveform
]);

echo $audio['audio_url'];
```

### 3. Storefront AI Copilot

```php
$answer = $client->copilot->consult([
    'question' => 'Does this monitor support HDR10 and 144Hz over HDMI 2.1?',
    'current_product' => [
        'id'    => 402,
        'title' => 'UltraWide 34-Inch Gaming Monitor',
        'price' => 799,
    ],
]);

echo $answer['reply'];
```

### 4. Review Sentiment Digest

```php
$digest = $client->reviews->generateDigest(402, [
    ['id' => 1, 'rating' => 5, 'content' => 'Outstanding color accuracy and high refresh rate.'],
    ['id' => 2, 'rating' => 4, 'content' => 'Great display, stand takes up a bit of desk depth.'],
]);

print_r($digest['pros']);
print_r($digest['cons']);
```

### 5. Inbound Site Events

Report storefront and CMS events to KavoshRank (leads, comments, forms, orders):

```php
$client->events->send('comment_created', [
    'comment_id' => 991,
    'author'     => 'John Doe',
    'content'    => 'Excellent post!',
]);
```

---

## Receiving Webhooks & Incoming Requests

Use `KavoshRank\Webhook\Receiver` to securely accept incoming actions from the KavoshRank Master Engine:

```php
use KavoshRank\Webhook\Receiver;
use KavoshRank\Webhook\WebhookEvent;

$receiver = new Receiver('YOUR_SITE_SECRET_TOKEN');

$receiver->on('publish_article', function (WebhookEvent $event) {
    $title = $event->get('title');
    $content = $event->get('content');

    // Save article in your custom database / CMS
    return ['status' => 'created', 'post_id' => 123];
});

$receiver->on('audit', function (WebhookEvent $event) {
    return ['status' => 'healthy'];
});

$response = $receiver->handleRequest();
echo json_encode($response);
```

---

## Error Handling

The SDK throws specific typed exceptions extending `KavoshRank\Exceptions\KavoshRankException`:

- `KavoshRank\Exceptions\AuthenticationException` (HTTP 401, 403)
- `KavoshRank\Exceptions\ValidationException` (HTTP 400, 422 - access field errors with `$e->getErrors()`)
- `KavoshRank\Exceptions\RateLimitException` (HTTP 429 - access retry interval with `$e->getRetryAfter()`)
- `KavoshRank\Exceptions\ServerException` (HTTP 500+)

```php
use KavoshRank\Exceptions\ValidationException;
use KavoshRank\Exceptions\AuthenticationException;
use KavoshRank\Exceptions\RateLimitException;

try {
    $client->articles->generate(['topic' => 'AI']);
} catch (ValidationException $e) {
    print_r($e->getErrors());
} catch (RateLimitException $e) {
    echo "Rate limited. Retry after " . $e->getRetryAfter() . " seconds.";
} catch (AuthenticationException $e) {
    echo "Invalid authentication credentials: " . $e->getMessage();
}
```

## Running Tests

Execute tests via PHP CLI:

```bash
php tests/run_tests.php
```
