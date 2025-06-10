<?php

namespace Modules\UserManagement\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PollingStation extends Model
{
    protected $fillable = [
        'name',
        'code',
        'ward_id',
        'location_description',
    ];

    /**
     * Get the ward that owns the polling station.
     */
    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class);
    }

    /**
     * Get the constituency through the ward.
     */
    public function constituency()
    {
        return $this->ward->constituency();
    }

    /**
     * Get the county through the ward's constituency.
     */
    public function county()
    {
        return $this->ward->constituency->county();
    }
}
