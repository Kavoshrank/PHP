<?php

declare(strict_types=1);

namespace KavoshRank\Services;

class AudioService extends BaseService
{
    /**
     * Generate an audio narration / podcast from article text.
     *
     * @param array{
     *   post_id: int,
     *   title: string,
     *   content: string,
     *   voice?: string,
     *   theme?: string,
     *   auto_inject?: bool,
     *   async?: bool
     * } $params
     * @return array
     */
    public function generate(array $params): array
    {
        $projectId = $this->config->getProjectId();
        $path = $projectId !== null
            ? "/api/v1/projects/{$projectId}/audio/generate"
            : '/api/v1/audio/generate';

        return $this->request('POST', $path, [], $params);
    }

    /**
     * List posts ready for audio narration or check their conversion state.
     */
    public function listArticles(array $query = []): array
    {
        $projectId = $this->config->getProjectId();
        $path = $projectId !== null
            ? "/api/v1/projects/{$projectId}/audio/articles"
            : '/api/v1/audio/articles';

        return $this->request('GET', $path, $query);
    }
}
