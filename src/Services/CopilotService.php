<?php

declare(strict_types=1);

namespace KavoshRank\Services;

class CopilotService extends BaseService
{
    /**
     * Consult the AI Storefront Copilot for pre-purchase customer assistance.
     *
     * @param array{
     *   question: string,
     *   current_product?: array,
     *   related_products?: array,
     *   shopper_id?: string
     * } $params
     * @return array
     */
    public function consult(array $params): array
    {
        $projectId = $this->config->getProjectId();
        $path = $projectId !== null
            ? "/api/v1/projects/{$projectId}/copilot/consult"
            : '/api/v1/plugin/copilot/consult';

        return $this->request('POST', $path, [], $params);
    }

    /**
     * Get copilot widget settings.
     */
    public function getSettings(): array
    {
        $projectId = $this->config->getProjectId();
        $path = $projectId !== null
            ? "/api/v1/projects/{$projectId}/copilot/settings"
            : '/api/v1/plugin/copilot/settings';

        return $this->request('GET', $path);
    }

    /**
     * Update copilot widget configuration.
     */
    public function updateSettings(array $settings): array
    {
        $projectId = $this->config->getProjectId();
        $path = $projectId !== null
            ? "/api/v1/projects/{$projectId}/copilot/settings"
            : '/api/v1/projects/0/copilot/settings';

        return $this->request('PUT', $path, [], $settings);
    }
}
