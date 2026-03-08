<?php

namespace Pterodactyl\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * \Pterodactyl\Models\RedemptionCode.
 *
 * @property int $id
 * @property string $code
 * @property string $type
 * @property int $value
 * @property int $uses_total
 * @property int $uses_remaining
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @mixin \Eloquent
 */
class RedemptionCode extends Model
{
    protected $table = 'redemption_codes';

    protected $guarded = ['id'];

    protected $casts = [
        'value' => 'integer',
        'uses_total' => 'integer',
        'uses_remaining' => 'integer',
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public static array $validationRules = [
        'code' => 'required|string|max:64',
        'type' => 'required|string|in:points,days',
        'value' => 'required|integer|min:1',
        'uses_total' => 'required|integer|min:1',
        'expires_at' => 'nullable|date',
    ];

    /**
     * Check whether this code is usable (active, not expired, has uses remaining).
     */
    public function isUsable(): bool
    {
        if (!$this->is_active) {
            return false;
        }
        if ($this->uses_remaining <= 0) {
            return false;
        }
        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\Pterodactyl\Models\RedemptionCodeUse, $this>
     */
    public function uses(): HasMany
    {
        return $this->hasMany(RedemptionCodeUse::class, 'redemption_code_id');
    }
}
