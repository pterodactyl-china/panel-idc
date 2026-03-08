<?php

namespace Pterodactyl\Console\Commands\Store;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Pterodactyl\Models\Server;
use Pterodactyl\Models\UserPoints;
use Pterodactyl\Models\PointTransaction;
use Pterodactyl\Services\Store\PointsCalculator;

/**
 * Deducts daily points from each user based on the servers they own.
 * Servers that are suspended or have no point cost assigned are skipped.
 * If a user runs out of points their server is suspended automatically.
 *
 * Schedule: daily (registered in Console\Kernel)
 */
class DeductDailyPointsCommand extends Command
{
    protected $signature = 'p:store:deduct-daily-points';

    protected $description = 'Deduct daily points from users based on their running servers\' resource costs.';

    public function __construct(private readonly PointsCalculator $calculator)
    {
        parent::__construct();
    }

    public function handle(): void
    {
        // Only process fully installed, non-suspended servers.
        $servers = Server::query()
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '');
            })
            ->get()
            ->filter(fn (Server $s) => $s->isInstalled() && !$s->isSuspended());

        $deductedCount = 0;
        $suspendedCount = 0;

        foreach ($servers as $server) {
            $cost = $server->points_per_day ?? $this->calculator->forServer($server);

            // Persist the calculated cost if not already stored.
            if (is_null($server->points_per_day)) {
                $server->forceFill(['points_per_day' => $cost])->save();
            }

            DB::transaction(function () use ($server, $cost, &$deductedCount, &$suspendedCount) {
                $points = UserPoints::firstOrCreate(
                    ['user_id' => $server->owner_id],
                    ['balance' => 0]
                );

                if ($points->balance < $cost) {
                    // Not enough points — suspend the server.
                    $server->forceFill(['status' => Server::STATUS_SUSPENDED])->save();
                    $suspendedCount++;

                    PointTransaction::create([
                        'user_id'     => $server->owner_id,
                        'amount'      => 0,
                        'type'        => 'spend',
                        'description' => "积分不足，服务器 [{$server->name}] 已被暂停。",
                    ]);
                } else {
                    $points->decrement('balance', $cost);

                    PointTransaction::create([
                        'user_id'     => $server->owner_id,
                        'amount'      => -$cost,
                        'type'        => 'spend',
                        'description' => "服务器 [{$server->name}] 每日扣除 {$cost} 积分。",
                    ]);

                    $deductedCount++;
                }
            });
        }

        $this->info("完成：已从 {$deductedCount} 台服务器扣除积分，已暂停 {$suspendedCount} 台积分不足的服务器。");
    }
}
