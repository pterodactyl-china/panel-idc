<?php

namespace Pterodactyl\Http\Controllers\Api\Client;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Pterodactyl\Models\Order;
use Pterodactyl\Models\Product;
use Pterodactyl\Models\UserPoints;
use Pterodactyl\Models\PointTransaction;
use Pterodactyl\Models\PaymentSetting;
use Pterodactyl\Exceptions\DisplayException;

class PaymentController extends ClientApiController
{
    /**
     * Create a new order for the given product.
     *
     * @throws \Pterodactyl\Exceptions\DisplayException
     */
    public function createOrder(Request $request): JsonResponse
    {
        $request->validate([
            'product_id'     => 'required|integer|exists:products,id',
            'payment_method' => 'required|string|in:alipay,alipay_face,wechat',
        ]);

        $method = $request->input('payment_method');

        // Check that the chosen payment method is actually enabled.
        if (!PaymentSetting::isEnabled($method)) {
            throw new DisplayException('所选支付方式当前不可用。');
        }

        $product = Product::where('id', $request->input('product_id'))
            ->where('is_active', true)
            ->firstOrFail();

        $order = Order::create([
            'order_no'       => date('YmdHis') . strtoupper(Str::random(8)),
            'user_id'        => $request->user()->id,
            'product_id'     => $product->id,
            'subject'        => $product->name,
            'amount'         => $product->price,
            'currency'       => $product->currency,
            'status'         => Order::STATUS_PENDING,
            'payment_method' => $method,
        ]);

        $paymentInfo = $this->buildPaymentInfo($order, $method);

        return new JsonResponse([
            'order_no'       => $order->order_no,
            'amount'         => $order->amount,
            'currency'       => $order->currency,
            'payment_method' => $order->payment_method,
            'payment_info'   => $paymentInfo,
        ]);
    }

    /**
     * Query the status of an order.
     * For pending orders the payment info is also returned so the user can re-display the QR code.
     */
    public function queryOrder(Request $request, string $orderNo): JsonResponse
    {
        $order = Order::where('order_no', $orderNo)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $response = [
            'order_no' => $order->order_no,
            'status'   => $order->status,
            'amount'   => $order->amount,
            'paid_at'  => $order->paid_at?->toIso8601String(),
        ];

        // Include payment_info so pending orders can show the payment screen again.
        if ($order->isPending() && $order->payment_method) {
            $response['payment_info'] = $this->buildPaymentInfo($order, $order->payment_method);
        }

        return new JsonResponse($response);
    }

    /**
     * Handle payment callback/notify from payment gateway.
     *
     * IMPORTANT: In production you MUST verify the cryptographic signature sent
     * by the payment provider (Alipay RSA2 / WeChat Pay HMAC-SHA256) before
     * processing any order fulfillment. Skipping this check allows anyone to
     * forge a successful-payment notification.
     */
    public function notify(Request $request, string $method): JsonResponse
    {
        $orderNo = $request->input('out_trade_no');
        $tradeNo = $request->input('trade_no') ?? $request->input('transaction_id');

        $order = Order::where('order_no', $orderNo)
            ->where('status', Order::STATUS_PENDING)
            ->first();

        if (!$order) {
            return new JsonResponse(['success' => false]);
        }

        DB::transaction(function () use ($order, $tradeNo) {
            $order->update([
                'status'             => Order::STATUS_PAID,
                'payment_trade_no'   => $tradeNo,
                'paid_at'            => now(),
            ]);

            $this->fulfillOrder($order);
        });

        return new JsonResponse(['success' => true]);
    }

    /**
     * Build payment gateway-specific information.
     * In production this would call the actual Alipay/WeChat SDK.
     */
    private function buildPaymentInfo(Order $order, string $method): array
    {
        return match ($method) {
            'alipay' => [
                'type'         => 'alipay_online',
                'instructions' => '请使用支付宝扫描下方二维码完成支付',
                'qr_placeholder' => 'https://qr.alipay.com/placeholder/' . $order->order_no,
            ],
            'alipay_face' => [
                'type'         => 'alipay_face_to_face',
                'instructions' => '请向收款方出示下方付款码，或使用支付宝扫码',
                'qr_placeholder' => PaymentSetting::get('alipay_face_code', '（收款码未配置）'),
            ],
            'wechat' => [
                'type'         => 'wechat_pay',
                'instructions' => '请使用微信扫描下方二维码完成支付',
                'qr_placeholder' => 'weixin://wxpay/bizpayurl?placeholder=' . $order->order_no,
            ],
            default => [],
        };
    }

    /**
     * Fulfill a paid order by granting the product's reward.
     */
    public function fulfillOrder(Order $order): void
    {
        if (!$order->product) {
            return;
        }

        $product = $order->product;

        if ($product->type === 'points') {
            $points = UserPoints::firstOrCreate(
                ['user_id' => $order->user_id],
                ['balance' => 0]
            );
            $points->increment('balance', $product->value);

            PointTransaction::create([
                'user_id'     => $order->user_id,
                'amount'      => $product->value,
                'type'        => 'earn',
                'description' => "购买商品 [{$product->name}] 获得积分，订单号：{$order->order_no}",
            ]);
        }
        // server and server_days types can be fulfilled here in the future
        // once the server provisioning integration is wired up.
    }
}
