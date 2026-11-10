<?php

declare(strict_types=1);

namespace KavoshRank\Services;

class ReviewService extends BaseService
{
    /**
     * Generate an automated sentiment digest from customer reviews.
     *
     * @param int|string $productId
     * @param array<array{id: int|string, rating?: int, content: string, created_at?: string}> $reviews
     * @param bool $forceRefresh
     * @return array
     */
    public function generateDigest(int|string $productId, array $reviews, bool $forceRefresh = false): array
    {
        $projectId = $this->config->getProjectId();
        $path = $projectId !== null
            ? "/api/v1/projects/{$projectId}/woo/review-digest"
            : '/api/v1/plugin/reviews/digest';

        $payload = [
            'product_id' => (int) $productId,
            'reviews' => $reviews,
            'force_refresh' => $forceRefresh,
        ];

        return $this->request('POST', $path, [], $payload);
    }
}
