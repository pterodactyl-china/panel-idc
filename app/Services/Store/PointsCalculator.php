<?php

namespace Pterodactyl\Services\Store;

use Pterodactyl\Models\Server;
use Pterodactyl\Models\Product;

/**
 * Calculates the daily points cost for a server based on its resource limits.
 *
 * Global rates (configurable via environment variables or app config):
 *   POINTS_CPU_PER_CORE    — points per 100% CPU core per day (default 10)
 *   POINTS_MEMORY_PER_GB   — points per GB of memory per day  (default 5)
 *   POINTS_DISK_PER_GB     — points per GB of disk per day    (default 1)
 *
 * Per-product overrides (set on the Product model):
 *   points_cpu_rate, points_memory_rate, points_disk_rate
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
     * @param  int|null  $cpuRate    Per-product CPU rate override (points per core/day)
     * @param  int|null  $memoryRate Per-product memory rate override (points per GB/day)
     * @param  int|null  $diskRate   Per-product disk rate override (points per GB/day)
     */
    public function calculate(
        int $cpu,
        int $memory,
        int $disk,
        ?int $cpuRate    = null,
        ?int $memoryRate = null,
        ?int $diskRate   = null,
    ): int {
        $cpuCores = max(0, $cpu)    / 100;
        $memoryGb = max(0, $memory) / 1024;
        $diskGb   = max(0, $disk)   / 1024;

        $cost = (int) ceil(
            $cpuCores  * ($cpuRate    ?? $this->cpuRatePerCore)
            + $memoryGb * ($memoryRate ?? $this->memoryRatePerGb)
            + $diskGb   * ($diskRate   ?? $this->diskRatePerGb)
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

    /**
     * Calculate the daily points cost using a Product's per-product rate overrides.
     * Falls back to global rates for fields left null.
     */
    public function forProduct(Product $product): int
    {
        return $this->calculate(
            $product->cpu    ?? 0,
            $product->memory ?? 0,
            $product->disk   ?? 0,
            $product->points_cpu_rate,
            $product->points_memory_rate,
            $product->points_disk_rate,
        );
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
