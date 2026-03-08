<?php

namespace Pterodactyl\Services\Store;

use Pterodactyl\Models\Server;

/**
 * Calculates the daily points cost for a server based on its resource limits.
 *
 * Rates (configurable via environment variables or app config):
 *   POINTS_CPU_PER_CORE    — points per 100% CPU core per day (default 10)
 *   POINTS_MEMORY_PER_GB   — points per GB of memory per day  (default 5)
 *   POINTS_DISK_PER_GB     — points per GB of disk per day    (default 1)
 */
class PointsCalculator
{
    public function __construct(
        private readonly int $cpuRatePerCore  = 10,
        private readonly int $memoryRatePerGb = 5,
        private readonly int $diskRatePerGb   = 1,
    ) {
    }

    /**
     * Calculate the daily points cost for the given resource limits.
     *
     * @param  int  $cpu    CPU limit in % (100 = 1 core)
     * @param  int  $memory Memory limit in MB
     * @param  int  $disk   Disk limit in MB
     */
    public function calculate(int $cpu, int $memory, int $disk): int
    {
        $cpuCores = max(0, $cpu)    / 100;
        $memoryGb = max(0, $memory) / 1024;
        $diskGb   = max(0, $disk)   / 1024;

        $cost = (int) ceil(
            $cpuCores  * $this->cpuRatePerCore
            + $memoryGb * $this->memoryRatePerGb
            + $diskGb   * $this->diskRatePerGb
        );

        // Minimum of 1 point per day for any active server.
        return max(1, $cost);
    }

    /**
     * Calculate and return the daily points cost for a Server model.
     */
    public function forServer(Server $server): int
    {
        return $this->calculate($server->cpu, $server->memory, $server->disk);
    }

    public function getCpuRatePerCore(): int
    {
        return $this->cpuRatePerCore;
    }

    public function getMemoryRatePerGb(): int
    {
        return $this->memoryRatePerGb;
    }

    public function getDiskRatePerGb(): int
    {
        return $this->diskRatePerGb;
    }
}
