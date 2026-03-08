<?php

namespace Pterodactyl\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * \Pterodactyl\Models\Product.
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string $type
 * @property int $value
 * @property float $price
 * @property string $currency
 * @property bool $is_active
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @mixin \Eloquent
 */
class Product extends Model
{
    protected $table = 'products';

    protected $guarded = ['id'];

    protected $casts = [
        'value' => 'integer',
        'price' => 'float',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public static array $validationRules = [
        'name' => 'required|string|max:191',
        'type' => 'required|string|in:points,server_days,custom',
        'value' => 'required|integer|min:0',
        'price' => 'required|numeric|min:0',
        'currency' => 'required|string|max:8',
        'is_active' => 'sometimes|boolean',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\Pterodactyl\Models\Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
