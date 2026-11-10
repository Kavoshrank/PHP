<?php

declare(strict_types=1);

namespace KavoshRank\Services;

class HealthService extends BaseService
{
    /**
     * Check KavoshRank backend health.
     */
    public function check(): array
    {
        return $this->request('GET', '/api/v1/health');
    }

    /**
     * Get latest published WordPress plugin / connector update information.
     */
    public function getUpdateInfo(): array
    {
        return $this->request('GET', '/api/v1/plugin/update-info');
    }
}
