<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentTypeFieldOption extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'field_id',
        'label',
        'value',
        'order_index',
    ];

    /**
     * Get the field that owns the option.
     */
    public function field()
    {
        return $this->belongsTo(ContentTypeField::class, 'field_id');
    }
}
