<?php

declare(strict_types=1);

namespace KavoshRank\Services;

class ArticleService extends BaseService
{
    /**
     * Generate an AI SEO article synchronously or queue it asynchronously.
     *
     * @param array{
     *   topic: string,
     *   word_count?: int,
     *   category_id?: int,
     *   category_name?: string,
     *   model?: string,
     *   status?: string,
     *   publish_status?: string,
     *   use_serp?: bool,
     *   include_video?: bool,
     *   extra_instructions?: string,
     *   featured_image_type?: string,
     *   async?: bool
     * } $params
     * @return array
     */
    public function generate(array $params): array
    {
        $projectId = $this->config->getProjectId();
        $path = $projectId !== null
            ? "/api/v1/projects/{$projectId}/articles/generate"
            : '/api/v1/articles/generate';

        return $this->request('POST', $path, [], $params);
    }

    /**
     * Suggest article titles based on seed keywords.
     */
    public function suggestTitles(string $keyword): array
    {
        $projectId = $this->config->getProjectId();
        $path = $projectId !== null
            ? "/api/v1/projects/{$projectId}/articles/title-suggestions"
            : '/api/v1/articles/title-suggestions';

        return $this->request('POST', $path, [], ['keyword' => $keyword]);
    }

    /**
     * Retrieve Google Suggest autocomplete variations.
     */
    public function googleSuggest(string $keyword): array
    {
        $projectId = $this->config->getProjectId();
        $path = $projectId !== null
            ? "/api/v1/projects/{$projectId}/articles/google-suggest"
            : '/api/v1/articles/google-suggest';

        return $this->request('GET', $path, ['keyword' => $keyword]);
    }

    /**
     * Get status of an asynchronous article generation job.
     */
    public function getJob(string $jobId): array
    {
        $projectId = $this->config->getProjectId();
        $path = $projectId !== null
            ? "/api/v1/projects/{$projectId}/articles/jobs/{$jobId}"
            : "/api/v1/articles/jobs/{$jobId}";

        return $this->request('GET', $path);
    }

    /**
     * Check if there is an ongoing active job for the current project.
     */
    public function getActiveJob(): array
    {
        $projectId = $this->config->getProjectId();
        $path = $projectId !== null
            ? "/api/v1/projects/{$projectId}/articles/active-job"
            : '/api/v1/articles/active-job';

        return $this->request('GET', $path);
    }

    /**
     * Dismiss the current active job.
     */
    public function dismissActiveJob(): array
    {
        $projectId = $this->config->getProjectId();
        $path = $projectId !== null
            ? "/api/v1/projects/{$projectId}/articles/active-job/dismiss"
            : '/api/v1/articles/active-job/dismiss';

        return $this->request('POST', $path);
    }

    /**
     * Publish an article by ID.
     */
    public function publish(int|string $articleId, array $options = []): array
    {
        $projectId = $this->config->getProjectId();
        $path = $projectId !== null
            ? "/api/v1/projects/{$projectId}/articles/{$articleId}/publish"
            : "/api/v1/articles/{$articleId}/publish";

        return $this->request('POST', $path, [], $options);
    }

    /**
     * List article generation history.
     */
    public function history(array $query = []): array
    {
        $projectId = $this->config->getProjectId();
        $path = $projectId !== null
            ? "/api/v1/projects/{$projectId}/articles/history"
            : '/api/v1/articles/history';

        return $this->request('GET', $path, $query);
    }

    /**
     * Get available categories for article generation.
     */
    public function categories(): array
    {
        $projectId = $this->config->getProjectId();
        $path = $projectId !== null
            ? "/api/v1/projects/{$projectId}/articles/categories"
            : '/api/v1/articles/categories';

        return $this->request('GET', $path);
    }

    /**
     * Get available AI image generation models.
     */
    public function imageModels(): array
    {
        $projectId = $this->config->getProjectId();
        $path = $projectId !== null
            ? "/api/v1/projects/{$projectId}/articles/ai-image-models"
            : '/api/v1/articles/ai-image-models';

        return $this->request('GET', $path);
    }
}
