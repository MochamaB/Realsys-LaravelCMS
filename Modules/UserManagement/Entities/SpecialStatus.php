<?php

namespace Modules\UserManagement\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SpecialStatus extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
    ];

    /**
     * Get the profiles with this special status.
     */
    public function profiles(): HasMany
    {
        return $this->hasMany(Profile::class);
    }
}
