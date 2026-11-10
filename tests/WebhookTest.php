<?php

declare(strict_types=1);

namespace KavoshRank\Tests;

use KavoshRank\Exceptions\AuthenticationException;
use KavoshRank\Webhook\Receiver;
use KavoshRank\Webhook\WebhookEvent;
use PHPUnit\Framework\TestCase;

class WebhookTest extends TestCase
{
    public function testVerifyTokenSucceedsWithValidToken(): void
    {
        $receiver = new Receiver('secret-site-token-abc');
        $this->assertTrue($receiver->verifyToken('secret-site-token-abc'));
        $this->assertFalse($receiver->verifyToken('wrong-token'));
        $this->assertFalse($receiver->verifyToken(''));
    }

    public function testHandleRequestDispatchesRegisteredAction(): void
    {
        $receiver = new Receiver('secret-site-token-abc');

        $dispatched = false;
        $receiver->on('publish_article', function (WebhookEvent $event) use (&$dispatched) {
            $dispatched = true;
            return ['published_id' => $event->get('article_id')];
        });

        $server = [
            'HTTP_X_KAVOSHRANK_SECRET_TOKEN' => 'secret-site-token-abc',
        ];
        $body = json_encode([
            'action' => 'publish_article',
            'article_id' => 999,
        ]);

        $res = $receiver->handleRequest($server, $body);

        $this->assertTrue($dispatched);
        $this->assertTrue($res['success']);
        $this->assertSame('publish_article', $res['action']);
        $this->assertSame(['published_id' => 999], $res['result']);
    }

    public function testHandleRequestRejectsMissingOrInvalidToken(): void
    {
        $this->expectException(AuthenticationException::class);
        $receiver = new Receiver('secret-site-token-abc');

        $server = [
            'HTTP_X_KAVOSHRANK_SECRET_TOKEN' => 'wrong-token',
        ];
        $receiver->handleRequest($server, '{"action":"ping"}');
    }
}
