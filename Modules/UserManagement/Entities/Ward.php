<?php

namespace Modules\UserManagement\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ward extends Model
{
    protected $fillable = [
        'name',
        'code',
        'constituency_id',
    ];

    /**
     * Get the constituency that owns the ward.
     */
    public function constituency(): BelongsTo
    {
        return $this->belongsTo(Constituency::class);
    }

    /**
     * Get the county through the constituency.
     */
    public function county()
    {
        return $this->constituency->county();
    }

    /**
     * Get the polling stations in this ward.
     */
    public function pollingStations(): HasMany
    {
        return $this->hasMany(PollingStation::class);
    }

    /**
     * Get the profiles associated with this ward.
     */
    public function profiles(): HasMany
    {
        return $this->hasMany(Profile::class);
    }
}
