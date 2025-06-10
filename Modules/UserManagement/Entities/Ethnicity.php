<?php

namespace Modules\UserManagement\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ethnicity extends Model
{
    protected $fillable = [
        'name',
        'code',
    ];

    /**
     * Get the profiles with this ethnicity.
     */
    public function profiles(): HasMany
    {
        return $this->hasMany(Profile::class);
    }
}
