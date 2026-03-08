<?php

namespace Pterodactyl\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * \Pterodactyl\Models\RedemptionCodeUse.
 *
 * @property int $id
 * @property int $redemption_code_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @mixin \Eloquent
 */
class RedemptionCodeUse extends Model
{
    protected $table = 'redemption_code_uses';

    protected $guarded = ['id'];

    protected $casts = [
        'redemption_code_id' => 'integer',
        'user_id' => 'integer',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Pterodactyl\Models\RedemptionCode, $this>
     */
    public function code(): BelongsTo
    {
        return $this->belongsTo(RedemptionCode::class, 'redemption_code_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Pterodactyl\Models\User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
