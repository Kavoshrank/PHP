<?php

declare(strict_types=1);

namespace KavoshRank\Services;

class EventService extends BaseService
{
    /**
     * Send an inbound site event (e.g. comment created, order placed, form submitted, lead received).
     *
     * @param string $event Event identifier (e.g. comment_created, order_placed, contact_form)
     * @param array $payload Event details and metadata
     * @return array
     */
    public function send(string $event, array $payload): array
    {
        return $this->request('POST', '/api/v1/plugin/events', [], [
            'event' => $event,
            'payload' => $payload,
        ]);
    }
}
