<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentFieldValue extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'content_item_id',
        'field_id',
        'value',
    ];

    /**
     * Get the content item that owns the field value.
     */
    public function contentItem()
    {
        return $this->belongsTo(ContentItem::class);
    }

    /**
     * Get the field that owns the field value.
     */
    public function field()
    {
        return $this->belongsTo(ContentTypeField::class, 'field_id');
    }

    /**
     * Get the formatted value based on field type.
     *
     * @return mixed
     */
    public function getFormattedValue()
    {
        if (!$this->field) {
            return $this->value;
        }

        switch ($this->field->type) {
            case 'date':
                return $this->value ? date('Y-m-d', strtotime($this->value)) : null;
            case 'datetime':
                return $this->value ? date('Y-m-d H:i:s', strtotime($this->value)) : null;
            case 'boolean':
                return (bool) $this->value;
            case 'number':
                return is_numeric($this->value) ? (float) $this->value : null;
            case 'json':
                return $this->value ? json_decode($this->value, true) : null;
            default:
                return $this->value;
        }
    }
}
