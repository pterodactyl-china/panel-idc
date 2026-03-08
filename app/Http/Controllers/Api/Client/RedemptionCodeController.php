<?php

namespace Pterodactyl\Http\Controllers\Api\Client;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Pterodactyl\Models\UserPoints;
use Pterodactyl\Models\PointTransaction;
use Pterodactyl\Models\RedemptionCode;
use Pterodactyl\Models\RedemptionCodeUse;
use Pterodactyl\Exceptions\DisplayException;

class RedemptionCodeController extends ClientApiController
{
    /**
     * Redeem a code for the authenticated user.
     *
     * @throws \Pterodactyl\Exceptions\DisplayException
     * @throws \Throwable
     */
    public function redeem(Request $request): JsonResponse
    {
        $request->validate(['code' => 'required|string|max:64']);

        $user = $request->user();
        $code = $request->input('code');

        $redemptionCode = RedemptionCode::where('code', $code)->first();

        if (!$redemptionCode || !$redemptionCode->isUsable()) {
            throw new DisplayException('兑换码无效或已过期。');
        }

        $alreadyUsed = RedemptionCodeUse::where('redemption_code_id', $redemptionCode->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($alreadyUsed) {
            throw new DisplayException('您已使用过此兑换码。');
        }

        DB::transaction(function () use ($redemptionCode, $user) {
            // Record usage
            RedemptionCodeUse::create([
                'redemption_code_id' => $redemptionCode->id,
                'user_id' => $user->id,
            ]);

            // Decrement uses remaining
            $redemptionCode->decrement('uses_remaining');

            // Apply reward based on type
            if ($redemptionCode->type === 'points') {
                $points = UserPoints::firstOrCreate(
                    ['user_id' => $user->id],
                    ['balance' => 0]
                );
                $points->increment('balance', $redemptionCode->value);

                PointTransaction::create([
                    'user_id' => $user->id,
                    'amount' => $redemptionCode->value,
                    'type' => 'redeem_code',
                    'description' => "兑换码 [{$redemptionCode->code}] 获得积分",
                ]);
            }
        });

        return new JsonResponse([
            'success' => true,
            'type' => $redemptionCode->type,
            'value' => $redemptionCode->value,
            'message' => $redemptionCode->type === 'points'
                ? "兑换成功！获得 {$redemptionCode->value} 积分。"
                : "兑换成功！",
        ]);
    }
}
