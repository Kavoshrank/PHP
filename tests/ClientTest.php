<?php

declare(strict_types=1);

namespace KavoshRank\Tests;

use KavoshRank\Client;
use KavoshRank\Exceptions\AuthenticationException;
use KavoshRank\Exceptions\RateLimitException;
use KavoshRank\Exceptions\ValidationException;
use KavoshRank\Http\Response;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    public function testClientInitializesWithConfig(): void
    {
        $mock = new MockHttpClient();
        $client = new Client([
            'site_token' => 'test-site-token-12345678901234567890',
            'api_key' => 'jwt-test-token',
            'project_id' => 42,
            'http_client' => $mock,
        ]);

        $this->assertSame('https://backend.kavoshrank.ir', $client->getConfig()->getBaseUrl());
        $this->assertSame(42, $client->getConfig()->getProjectId());
        $this->assertSame('test-site-token-12345678901234567890', $client->getConfig()->getSiteToken());
    }

    public function testArticleGenerateSendsCorrectHeadersAndPayload(): void
    {
        $mock = new MockHttpClient();
        $mock->nextResponse = new Response(200, [], json_encode([
            'job_id' => 'job_abc123',
            'status' => 'queued',
        ]));

        $client = new Client([
            'site_token' => 'my-token',
            'project_id' => 10,
            'http_client' => $mock,
        ]);

        $res = $client->articles->generate([
            'topic' => 'AI in Healthcare',
            'word_count' => 1200,
        ]);

        $this->assertSame('job_abc123', $res['job_id']);
        $this->assertCount(1, $mock->requests);
        $req = $mock->requests[0];
        $this->assertSame('POST', $req['method']);
        $this->assertSame('https://backend.kavoshrank.ir/api/v1/projects/10/articles/generate', $req['url']);
        $this->assertSame('my-token', $req['headers']['X-KavoshRank-Secret-Token']);

        $decodedBody = json_decode($req['body'], true);
        $this->assertSame('AI in Healthcare', $decodedBody['topic']);
        $this->assertSame(1200, $decodedBody['word_count']);
    }

    public function testAudioGenerateHandlesRequest(): void
    {
        $mock = new MockHttpClient();
        $mock->nextResponse = new Response(200, [], json_encode([
            'audio_url' => 'https://cdn.kavoshrank.ir/audio/sample.mp3',
            'post_id' => 55,
        ]));

        $client = new Client([
            'site_token' => 'my-token',
            'project_id' => 10,
            'http_client' => $mock,
        ]);

        $res = $client->audio->generate([
            'post_id' => 55,
            'title' => 'Sample Article',
            'content' => 'Full article content for narration.',
        ]);

        $this->assertSame('https://cdn.kavoshrank.ir/audio/sample.mp3', $res['audio_url']);
    }

    public function testAuthenticationExceptionThrownOn401(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionCode(401);

        $mock = new MockHttpClient();
        $mock->nextResponse = new Response(401, [], json_encode([
            'error' => 'Invalid site token',
        ]));

        $client = new Client([
            'site_token' => 'invalid-token',
            'http_client' => $mock,
        ]);

        $client->copilot->consult(['question' => 'How does this work?']);
    }

    public function testValidationExceptionThrownOn400(): void
    {
        $mock = new MockHttpClient();
        $mock->nextResponse = new Response(400, [], json_encode([
            'error' => 'Validation error',
            'errors' => ['topic' => 'Topic is required'],
        ]));

        $client = new Client([
            'site_token' => 'my-token',
            'http_client' => $mock,
        ]);

        try {
            $client->articles->generate(['word_count' => 500]);
            $this->fail('Expected ValidationException was not thrown');
        } catch (ValidationException $e) {
            $this->assertSame(400, $e->getStatusCode());
            $this->assertSame(['topic' => 'Topic is required'], $e->getErrors());
        }
    }

    public function testRateLimitExceptionThrownOn429(): void
    {
        $mock = new MockHttpClient();
        $mock->nextResponse = new Response(429, ['Retry-After' => '60'], json_encode([
            'error' => 'Too many requests',
        ]));

        $client = new Client([
            'site_token' => 'my-token',
            'http_client' => $mock,
        ]);

        try {
            $client->events->send('comment_created', ['comment_id' => 12]);
            $this->fail('Expected RateLimitException was not thrown');
        } catch (RateLimitException $e) {
            $this->assertSame(429, $e->getStatusCode());
            $this->assertSame(60, $e->getRetryAfter());
        }
    }
}
