<?php

namespace Modules\UserManagement\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class Membership extends Model
{
    protected $fillable = [
        'membership_number',
        'user_id',
        'start_date',
        'end_date',
        'status',
        'payment_status',
        'membership_type',
        'fee_amount',
        'payment_date',
        'payment_method',
        'payment_reference',
        'issued_card',
        'card_issue_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'payment_date' => 'date',
        'card_issue_date' => 'date',
        'issued_card' => 'boolean',
        'fee_amount' => 'decimal:2',
    ];

    /**
     * Get the user that owns the membership.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the profile for this membership.
     */
    public function profile()
    {
        return $this->belongsTo(Profile::class, 'user_id', 'user_id');
    }
}
