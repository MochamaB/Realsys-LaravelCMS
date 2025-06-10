<?php

namespace Modules\UserManagement\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProfileType extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'dashboard_route',
    ];

    /**
     * Get the profiles with this profile type.
     */
    public function profiles(): HasMany
    {
        return $this->hasMany(Profile::class);
    }
}
