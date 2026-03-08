<?php

namespace Pterodactyl\Http\Controllers\Api\Client;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Pterodactyl\Models\UserPoints;
use Pterodactyl\Models\PointTransaction;

class PointsController extends ClientApiController
{
    /**
     * Return the authenticated user's points balance and recent transactions.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $points = UserPoints::firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0]
        );

        $transactions = PointTransaction::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return new JsonResponse([
            'balance' => $points->balance,
            'transactions' => $transactions->map(fn ($t) => [
                'id' => $t->id,
                'amount' => $t->amount,
                'type' => $t->type,
                'description' => $t->description,
                'created_at' => $t->created_at?->toIso8601String(),
            ]),
        ]);
    }
}
