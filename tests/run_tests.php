<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/Config.php';
require_once __DIR__ . '/../src/Exceptions/KavoshRankException.php';
require_once __DIR__ . '/../src/Exceptions/AuthenticationException.php';
require_once __DIR__ . '/../src/Exceptions/ValidationException.php';
require_once __DIR__ . '/../src/Exceptions/RateLimitException.php';
require_once __DIR__ . '/../src/Exceptions/ServerException.php';
require_once __DIR__ . '/../src/Http/HttpClientInterface.php';
require_once __DIR__ . '/../src/Http/Response.php';
require_once __DIR__ . '/../src/Http/CurlHttpClient.php';
require_once __DIR__ . '/../src/Services/BaseService.php';
require_once __DIR__ . '/../src/Services/ArticleService.php';
require_once __DIR__ . '/../src/Services/AudioService.php';
require_once __DIR__ . '/../src/Services/CopilotService.php';
require_once __DIR__ . '/../src/Services/ReviewService.php';
require_once __DIR__ . '/../src/Services/EventService.php';
require_once __DIR__ . '/../src/Services/HealthService.php';
require_once __DIR__ . '/../src/Webhook/WebhookEvent.php';
require_once __DIR__ . '/../src/Webhook/Receiver.php';
require_once __DIR__ . '/../src/Client.php';
require_once __DIR__ . '/MockHttpClient.php';

use KavoshRank\Client;
use KavoshRank\Exceptions\AuthenticationException;
use KavoshRank\Exceptions\RateLimitException;
use KavoshRank\Exceptions\ValidationException;
use KavoshRank\Http\Response;
use KavoshRank\Tests\MockHttpClient;
use KavoshRank\Webhook\Receiver;
use KavoshRank\Webhook\WebhookEvent;

function assertCondition(bool $cond, string $msg): void {
    if (!$cond) {
        throw new Exception("Assertion Failed: {$msg}");
    }
}

echo "Running PHP SDK Tests...\n";

// Test 1: Client initialization
$mock = new MockHttpClient();
$client = new Client([
    'site_token' => 'test-site-token-12345678901234567890',
    'api_key' => 'jwt-test-token',
    'project_id' => 42,
    'http_client' => $mock,
]);
assertCondition($client->getConfig()->getBaseUrl() === 'https://backend.kavoshrank.ir', 'Base URL match');
assertCondition($client->getConfig()->getProjectId() === 42, 'Project ID match');
assertCondition($client->getConfig()->getSiteToken() === 'test-site-token-12345678901234567890', 'Site token match');
echo "PASS: Client initialization\n";

// Test 2: Article generation
$mock->nextResponse = new Response(200, [], json_encode(['job_id' => 'job_123']));
$res = $client->articles->generate(['topic' => 'AI SEO Optimization', 'word_count' => 1500]);
assertCondition($res['job_id'] === 'job_123', 'Article job ID match');
assertCondition($mock->requests[0]['headers']['X-KavoshRank-Secret-Token'] === 'test-site-token-12345678901234567890', 'Header match');
echo "PASS: Article generation\n";

// Test 3: Audio generation
$mock->nextResponse = new Response(200, [], json_encode(['audio_url' => 'https://cdn.example.com/audio.mp3']));
$res = $client->audio->generate(['post_id' => 10, 'title' => 'T', 'content' => 'C']);
assertCondition($res['audio_url'] === 'https://cdn.example.com/audio.mp3', 'Audio URL match');
echo "PASS: Audio generation\n";

// Test 4: Copilot consult
$mock->nextResponse = new Response(200, [], json_encode(['answer' => 'Hello']));
$res = $client->copilot->consult(['question' => 'What is this?']);
assertCondition($res['answer'] === 'Hello', 'Copilot answer match');
echo "PASS: Copilot consult\n";

// Test 5: Review digest
$mock->nextResponse = new Response(200, [], json_encode(['status' => 'ok', 'consensus' => 'positive']));
$res = $client->reviews->generateDigest(101, [['id' => 1, 'content' => 'Great product', 'rating' => 5]]);
assertCondition($res['status'] === 'ok', 'Review digest match');
echo "PASS: Review digest\n";

// Test 6: Event send
$mock->nextResponse = new Response(200, [], json_encode(['success' => true]));
$res = $client->events->send('comment_created', ['comment_id' => 55]);
assertCondition($res['success'] === true, 'Event send match');
echo "PASS: Event send\n";

// Test 7: Validation error on 400
$mock->nextResponse = new Response(400, [], json_encode(['error' => 'Bad request', 'errors' => ['topic' => 'required']]));
try {
    $client->articles->generate(['word_count' => 100]);
    assertCondition(false, 'Expected ValidationException');
} catch (ValidationException $e) {
    assertCondition($e->getStatusCode() === 400, 'Status code 400');
    assertCondition($e->getErrors() === ['topic' => 'required'], 'Validation errors match');
}
echo "PASS: ValidationException\n";

// Test 8: RateLimitException on 429
$mock->nextResponse = new Response(429, ['Retry-After' => '45'], json_encode(['error' => 'Too many requests']));
try {
    $client->health->check();
    assertCondition(false, 'Expected RateLimitException');
} catch (RateLimitException $e) {
    assertCondition($e->getStatusCode() === 429, 'Status code 429');
    assertCondition($e->getRetryAfter() === 45, 'Retry-After 45');
}
echo "PASS: RateLimitException\n";

// Test 9: Webhook receiver
$receiver = new Receiver('secret-12345');
assertCondition($receiver->verifyToken('secret-12345') === true, 'Valid token');
assertCondition($receiver->verifyToken('wrong') === false, 'Invalid token');

$dispatched = false;
$receiver->on('publish_article', function (WebhookEvent $ev) use (&$dispatched) {
    $dispatched = true;
    return ['received_id' => $ev->get('id')];
});

$result = $receiver->handleRequest(
    ['HTTP_X_KAVOSHRANK_SECRET_TOKEN' => 'secret-12345'],
    json_encode(['action' => 'publish_article', 'id' => 777])
);

assertCondition($dispatched === true, 'Webhook dispatched');
assertCondition($result['success'] === true, 'Webhook success');
assertCondition($result['result']['received_id'] === 777, 'Result match');
echo "PASS: Webhook Receiver\n";

echo "\nAll PHP SDK tests passed successfully!\n";
