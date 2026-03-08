<?php

namespace Pterodactyl\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * \Pterodactyl\Models\Ticket.
 *
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string $content
 * @property string $status  open|in_progress|closed
 * @property string $priority  low|normal|high|urgent
 * @property \Illuminate\Support\Carbon|null $last_reply_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @mixin \Eloquent
 */
class Ticket extends Model
{
    public const STATUS_OPEN        = 'open';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_CLOSED      = 'closed';

    public const PRIORITY_LOW    = 'low';
    public const PRIORITY_NORMAL = 'normal';
    public const PRIORITY_HIGH   = 'high';
    public const PRIORITY_URGENT = 'urgent';

    protected $table = 'tickets';

    protected $guarded = ['id'];

    protected $casts = [
        'user_id'       => 'integer',
        'last_reply_at' => 'datetime',
    ];

    public static array $validationRules = [
        'title'    => 'required|string|max:191',
        'content'  => 'required|string|max:65535',
        'priority' => 'sometimes|string|in:low,normal,high,urgent',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Pterodactyl\Models\User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\Pterodactyl\Models\TicketReply, $this>
     */
    public function replies(): HasMany
    {
        return $this->hasMany(TicketReply::class);
    }
}
