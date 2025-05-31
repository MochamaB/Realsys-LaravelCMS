<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContentItem extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'content_type_id',
        'status',
        'published_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'published_at' => 'datetime',
    ];

    /**
     * Get the content type that owns the content item.
     */
    public function contentType(): BelongsTo
    {
        return $this->belongsTo(ContentType::class);
    }

    /**
     * Get the field values for the content item.
     */
    public function fieldValues(): HasMany
    {
        return $this->hasMany(ContentFieldValue::class);
    }

    /**
     * Get the user that created the content item.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that last updated the content item.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get a field value by key.
     *
     * @param string $key
     * @return mixed
     */
    public function getFieldValue($key)
    {
        $field = $this->contentType->fields()->where('key', $key)->first();
        
        if (!$field) {
            return null;
        }
        
        $fieldValue = $this->fieldValues()->where('field_id', $field->id)->first();
        
        return $fieldValue ? $fieldValue->value : null;
    }

    /**
     * Set a field value by key.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function setFieldValue($key, $value)
    {
        $field = $this->contentType->fields()->where('key', $key)->first();
        
        if (!$field) {
            return;
        }
        
        $fieldValue = $this->fieldValues()->where('field_id', $field->id)->first();
        
        if ($fieldValue) {
            $fieldValue->update(['value' => $value]);
        } else {
            $this->fieldValues()->create([
                'field_id' => $field->id,
                'value' => $value,
            ]);
        }
    }

    /**
     * Scope a query to only include published content items.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where(function ($query) {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }
}
