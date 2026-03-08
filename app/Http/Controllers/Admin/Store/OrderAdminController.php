<?php

namespace Pterodactyl\Http\Controllers\Admin\Store;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Prologue\Alerts\AlertsMessageBag;
use Pterodactyl\Models\Order;
use Pterodactyl\Http\Controllers\Api\Client\PaymentController;
use Pterodactyl\Http\Controllers\Controller;

class OrderAdminController extends Controller
{
    public function __construct(
        private readonly AlertsMessageBag $alert,
        private readonly PaymentController $payment,
    ) {
    }

    public function index(Request $request): View
    {
        $query = Order::query()->with(['user', 'product'])->orderByDesc('created_at');

        if ($search = $request->input('filter.order_no')) {
            $query->where('order_no', 'like', "%{$search}%");
        }
        if ($status = $request->input('filter.status')) {
            $query->where('status', $status);
        }

        return view('admin.store.orders.index', [
            'orders' => $query->paginate(30),
        ]);
    }

    /**
     * Manually mark an order as paid and trigger fulfillment.
     */
    public function complete(int $id): RedirectResponse
    {
        $order = Order::findOrFail($id);

        if ($order->isPaid()) {
            $this->alert->warning('该订单已经是已支付状态。')->flash();
            return redirect()->route('admin.store.orders');
        }

        DB::transaction(function () use ($order) {
            $order->update([
                'status'  => Order::STATUS_PAID,
                'paid_at' => now(),
            ]);
            $this->payment->fulfillOrder($order);
        });

        $this->alert->success("订单 [{$order->order_no}] 已手动标记为已支付并完成发货。")->flash();

        return redirect()->route('admin.store.orders');
    }

    /**
     * Cancel a pending or paid order.
     */
    public function cancel(int $id): RedirectResponse
    {
        $order = Order::findOrFail($id);
        $order->update(['status' => Order::STATUS_CANCELLED]);

        $this->alert->success("订单 [{$order->order_no}] 已取消。")->flash();

        return redirect()->route('admin.store.orders');
    }

    /**
     * Mark an order as refunded.
     */
    public function refund(int $id): RedirectResponse
    {
        $order = Order::findOrFail($id);
        $order->update(['status' => Order::STATUS_REFUNDED]);

        $this->alert->success("订单 [{$order->order_no}] 已标记为已退款。")->flash();

        return redirect()->route('admin.store.orders');
    }

    /**
     * Permanently delete an order.
     */
    public function destroy(int $id): RedirectResponse
    {
        Order::findOrFail($id)->delete();

        $this->alert->success('订单已删除。')->flash();

        return redirect()->route('admin.store.orders');
    }
}
