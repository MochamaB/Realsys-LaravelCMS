<?php

namespace Modules\UserManagement\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class County extends Model
{
    protected $fillable = [
        'name',
        'code',
    ];

    /**
     * Get the constituencies in this county.
     */
    public function constituencies(): HasMany
    {
        return $this->hasMany(Constituency::class);
    }

    /**
     * Get the profiles associated with this county.
     */
    public function profiles(): HasMany
    {
        return $this->hasMany(Profile::class);
    }

    /**
     * Get all wards within this county through constituencies.
     */
    public function wards()
    {
        return $this->hasManyThrough(Ward::class, Constituency::class);
    }
}
