<?php

namespace Pterodactyl\Http\Controllers\Admin\Store;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Prologue\Alerts\AlertsMessageBag;
use Pterodactyl\Models\RedemptionCode;
use Pterodactyl\Http\Controllers\Controller;

class RedemptionCodeAdminController extends Controller
{
    public function __construct(private readonly AlertsMessageBag $alert)
    {
    }

    public function index(): View
    {
        return view('admin.store.redemption-codes.index', [
            'codes' => RedemptionCode::query()
                ->withCount('uses')
                ->orderByDesc('created_at')
                ->paginate(50),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code'          => 'nullable|string|max:64|unique:redemption_codes,code',
            'type'          => 'required|string|in:points,days',
            'value'         => 'required|integer|min:1',
            'uses_total'    => 'required|integer|min:1',
            'expires_at'    => 'nullable|date',
        ]);

        // Auto-generate a code if not provided.
        $data['code'] = $data['code'] ?: strtoupper(Str::random(12));
        $data['uses_remaining'] = $data['uses_total'];

        RedemptionCode::create($data);

        $this->alert->success("兑换码 [{$data['code']}] 已创建。")->flash();

        return redirect()->route('admin.store.redemption-codes');
    }

    public function destroy(int $id): RedirectResponse
    {
        RedemptionCode::findOrFail($id)->delete();

        $this->alert->success('兑换码已删除。')->flash();

        return redirect()->route('admin.store.redemption-codes');
    }
}
