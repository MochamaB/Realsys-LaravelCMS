<?php

namespace Modules\UserManagement\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Constituency extends Model
{
    protected $fillable = [
        'name',
        'code',
        'county_id',
    ];

    /**
     * Get the county that owns the constituency.
     */
    public function county(): BelongsTo
    {
        return $this->belongsTo(County::class);
    }

    /**
     * Get the wards in this constituency.
     */
    public function wards(): HasMany
    {
        return $this->hasMany(Ward::class);
    }

    /**
     * Get the profiles associated with this constituency.
     */
    public function profiles(): HasMany
    {
        return $this->hasMany(Profile::class);
    }

    /**
     * Get all polling stations within this constituency through wards.
     */
    public function pollingStations()
    {
        return $this->hasManyThrough(PollingStation::class, Ward::class);
    }
}
