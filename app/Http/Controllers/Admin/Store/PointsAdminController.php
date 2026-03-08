<?php

namespace Pterodactyl\Http\Controllers\Admin\Store;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Prologue\Alerts\AlertsMessageBag;
use Pterodactyl\Models\User;
use Pterodactyl\Models\UserPoints;
use Pterodactyl\Models\PointTransaction;
use Pterodactyl\Http\Controllers\Controller;

class PointsAdminController extends Controller
{
    public function __construct(private readonly AlertsMessageBag $alert)
    {
    }

    public function index(Request $request): View
    {
        $query = User::query()
            ->select('users.*')
            ->selectRaw('COALESCE(up.balance, 0) as points_balance')
            ->leftJoin('user_points as up', 'up.user_id', '=', 'users.id');

        if ($search = $request->input('filter.email')) {
            $query->where('users.email', 'like', "%{$search}%");
        }

        return view('admin.store.points.index', [
            'users' => $query->orderByDesc('points_balance')->paginate(50),
        ]);
    }

    public function adjust(Request $request, int $userId): RedirectResponse
    {
        $data = $request->validate([
            'amount'      => 'required|integer|not_in:0',
            'description' => 'nullable|string|max:191',
        ]);

        $user = User::findOrFail($userId);

        DB::transaction(function () use ($user, $data) {
            $points = UserPoints::firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => 0]
            );
            $points->increment('balance', $data['amount']);

            PointTransaction::create([
                'user_id'     => $user->id,
                'amount'      => $data['amount'],
                'type'        => 'admin_adjust',
                'description' => $data['description'] ?? '管理员调整',
            ]);
        });

        $verb = $data['amount'] > 0 ? '增加' : '减少';
        $this->alert->success("已为用户 {$user->email} {$verb} " . abs($data['amount']) . " 积分。")->flash();

        return redirect()->route('admin.store.points');
    }
}
