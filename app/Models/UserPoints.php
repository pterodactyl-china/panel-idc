<?php

namespace Pterodactyl\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * \Pterodactyl\Models\UserPoints.
 *
 * @property int $id
 * @property int $user_id
 * @property int $balance
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @mixin \Eloquent
 */
class UserPoints extends Model
{
    protected $table = 'user_points';

    protected $guarded = ['id'];

    protected $casts = [
        'user_id' => 'integer',
        'balance' => 'integer',
    ];

    public static array $validationRules = [
        'user_id' => 'required|integer|exists:users,id',
        'balance' => 'required|integer',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Pterodactyl\Models\User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\Pterodactyl\Models\PointTransaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(PointTransaction::class, 'user_id', 'user_id');
    }
}
