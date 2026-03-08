<?php

namespace Pterodactyl\Http\Controllers\Api\Client;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Pterodactyl\Models\Order;

class OrderController extends ClientApiController
{
    /**
     * List all orders for the authenticated user (newest first).
     */
    public function index(Request $request): JsonResponse
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->with('product')
            ->orderByDesc('created_at')
            ->paginate(20);

        return new JsonResponse([
            'data' => $orders->map(fn (Order $o) => $this->transform($o)),
            'meta' => [
                'total'        => $orders->total(),
                'current_page' => $orders->currentPage(),
                'last_page'    => $orders->lastPage(),
            ],
        ]);
    }

    /**
     * Cancel a pending order belonging to the authenticated user.
     */
    public function cancel(Request $request, string $orderNo): JsonResponse
    {
        $order = Order::where('order_no', $orderNo)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        if (!$order->isPending()) {
            return new JsonResponse(['message' => '只有待支付订单可以取消。'], 422);
        }

        $order->update(['status' => Order::STATUS_CANCELLED]);

        return new JsonResponse(['success' => true]);
    }

    private function transform(Order $o): array
    {
        return [
            'order_no'       => $o->order_no,
            'subject'        => $o->subject,
            'product_name'   => $o->product?->name,
            'amount'         => $o->amount,
            'currency'       => $o->currency,
            'status'         => $o->status,
            'payment_method' => $o->payment_method,
            'paid_at'        => $o->paid_at?->toIso8601String(),
            'created_at'     => $o->created_at?->toIso8601String(),
        ];
    }
}
