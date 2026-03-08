<?php

namespace Pterodactyl\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * \Pterodactyl\Models\TicketReply.
 *
 * @property int $id
 * @property int $ticket_id
 * @property int $user_id
 * @property string $content
 * @property bool $is_staff
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @mixin \Eloquent
 */
class TicketReply extends Model
{
    protected $table = 'ticket_replies';

    protected $guarded = ['id'];

    protected $casts = [
        'ticket_id' => 'integer',
        'user_id'   => 'integer',
        'is_staff'  => 'boolean',
    ];

    public static array $validationRules = [
        'content' => 'required|string|max:65535',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Pterodactyl\Models\Ticket, $this>
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Pterodactyl\Models\User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
