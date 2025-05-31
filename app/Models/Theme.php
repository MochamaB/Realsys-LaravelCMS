<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Theme extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'identifier',
        'description',
        'version',
        'author',
        'website',
        'is_active',
        'screenshot',
        'settings'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'json'
    ];

    /**
     * Get the templates for this theme.
     */
    public function templates(): HasMany
    {
        return $this->hasMany(Template::class);
    }
    
    /**
     * Get the widgets for this theme.
     */
    public function widgets(): HasMany
    {
        return $this->hasMany(Widget::class);
    }
}
