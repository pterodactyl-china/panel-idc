<?php

namespace Pterodactyl\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * \Pterodactyl\Models\Order.
 *
 * @property int $id
 * @property string $order_no
 * @property int $user_id
 * @property int|null $product_id
 * @property string $subject
 * @property float $amount
 * @property string $currency
 * @property string $status
 * @property string|null $payment_method
 * @property string|null $payment_trade_no
 * @property \Illuminate\Support\Carbon|null $paid_at
 * @property array|null $metadata
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @mixin \Eloquent
 */
class Order extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_REFUNDED = 'refunded';

    public const PAYMENT_ALIPAY = 'alipay';
    public const PAYMENT_ALIPAY_FACE = 'alipay_face';
    public const PAYMENT_WECHAT = 'wechat';

    protected $table = 'orders';

    protected $guarded = ['id'];

    protected $casts = [
        'user_id' => 'integer',
        'product_id' => 'integer',
        'amount' => 'float',
        'metadata' => 'array',
        'paid_at' => 'datetime',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Pterodactyl\Models\User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Pterodactyl\Models\Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }
}
