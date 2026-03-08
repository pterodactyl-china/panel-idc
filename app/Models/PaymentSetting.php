<?php

namespace Pterodactyl\Models;

use Illuminate\Database\Eloquent\Model as BaseModel;

/**
 * Key-value store for payment gateway settings.
 *
 * @property int $id
 * @property string $key
 * @property string|null $value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @mixin \Eloquent
 */
class PaymentSetting extends BaseModel
{
    protected $table = 'payment_settings';

    protected $guarded = ['id'];

    /**
     * Retrieve a specific payment setting by key, returning $default if not set.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $row = static::query()->where('key', $key)->first();

        return $row?->value ?? $default;
    }

    /**
     * Set a payment setting value, upserting if needed.
     */
    public static function set(string $key, mixed $value): void
    {
        static::query()->updateOrInsert(
            ['key' => $key],
            ['value' => $value, 'updated_at' => now()]
        );
    }

    /**
     * Return all settings as an associative array.
     */
    public static function allAsArray(): array
    {
        return static::query()->pluck('value', 'key')->all();
    }

    /**
     * Determine whether a given payment method is enabled.
     */
    public static function isEnabled(string $method): bool
    {
        return (bool) static::get($method . '_enabled', false);
    }

    /**
     * Return an array of currently enabled payment method keys.
     */
    public static function enabledMethods(): array
    {
        $methods = [];
        foreach (['alipay', 'alipay_face', 'wechat'] as $method) {
            if (static::isEnabled($method)) {
                $methods[] = $method;
            }
        }
        return $methods;
    }
}
