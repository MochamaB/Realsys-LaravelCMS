<?php

namespace Modules\UserManagement\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MobileProvider extends Model
{
    protected $fillable = [
        'name',
        'prefix',
        'logo_path',
    ];

    /**
     * Get the profiles with this mobile provider.
     */
    public function profiles(): HasMany
    {
        return $this->hasMany(Profile::class);
    }
}
