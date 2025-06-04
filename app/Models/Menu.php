<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'location',
        'description',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all items for this menu.
     */
    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }

    /**
     * Get all root-level items for this menu.
     */
    public function rootItems(): HasMany
    {
        return $this->hasMany(MenuItem::class)
            ->whereNull('parent_id')
            ->orderBy('position');
    }

    /**
     * Get an active menu by location.
     *
     * @param string $location
     * @return self|null
     */
    public static function getByLocation(string $location): ?self
    {
        return static::where('location', $location)
            ->where('is_active', true)
            ->first();
    }
    
    /**
     * Get an active menu by location with its items loaded.
     *
     * @param string $location
     * @return self|null
     */
    public static function getByLocationWithItems(string $location): ?self
    {
        return static::where('location', $location)
            ->where('is_active', true)
            ->with(['rootItems' => function($query) {
                $query->where('is_active', true)->orderBy('position');
            }, 'rootItems.children' => function($query) {
                $query->where('is_active', true)->orderBy('position');
            }])
            ->first();
    }
}
