<?php

namespace Pterodactyl\Http\Controllers\Admin\Store;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Pterodactyl\Models\PaymentSetting;
use Pterodactyl\Http\Controllers\Controller;

class PaymentSettingsController extends Controller
{
    public function __construct(private readonly AlertsMessageBag $alert)
    {
    }

    public function index(): View
    {
        return view('admin.store.payment-settings.index', [
            'settings' => PaymentSetting::allAsArray(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $keys = [
            'alipay_enabled', 'alipay_app_id', 'alipay_private_key', 'alipay_public_key',
            'alipay_face_enabled', 'alipay_face_code',
            'wechat_enabled', 'wechat_app_id', 'wechat_mch_id', 'wechat_api_key',
        ];

        foreach ($keys as $key) {
            // Checkboxes send no value when unchecked, so treat absence as '0'.
            if (str_ends_with($key, '_enabled')) {
                PaymentSetting::set($key, $request->has($key) ? '1' : '0');
            } else {
                PaymentSetting::set($key, $request->input($key, ''));
            }
        }

        $this->alert->success('支付设置已保存。')->flash();

        return redirect()->route('admin.store.payment-settings');
    }
}
