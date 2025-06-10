<?php

namespace Modules\UserManagement\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Religion extends Model
{
    protected $fillable = [
        'name',
    ];

    /**
     * Get the profiles with this religion.
     */
    public function profiles(): HasMany
    {
        return $this->hasMany(Profile::class);
    }
}
