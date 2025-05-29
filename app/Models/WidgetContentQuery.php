<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WidgetContentQuery extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'content_type_id',
        'limit',
        'offset',
        'order_by',
        'order_direction'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'limit' => 'integer',
        'offset' => 'integer'
    ];

    /**
     * Get the content type that this query targets.
     */
    public function contentType()
    {
        return $this->belongsTo(ContentType::class);
    }

    /**
     * Get the filters for this query.
     */
    public function filters()
    {
        return $this->hasMany(WidgetContentQueryFilter::class, 'query_id');
    }

    /**
     * Get the widgets using this query.
     */
    public function widgets()
    {
        return $this->hasMany(Widget::class, 'content_query_id');
    }

    /**
     * Execute the content query and return the results.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function executeQuery()
    {
        if (!$this->contentType) {
            return collect([]);
        }

        $query = ContentItem::where('content_type_id', $this->content_type_id);
        
        // Apply filters
        foreach ($this->filters as $filter) {
            $query = $filter->applyToQuery($query);
        }
        
        // Apply sorting
        if ($this->order_by) {
            $query->orderBy($this->order_by, $this->order_direction);
        }
        
        // Apply limit and offset
        if ($this->limit) {
            $query->limit($this->limit);
        }
        
        if ($this->offset) {
            $query->offset($this->offset);
        }
        
        return $query->get();
    }
}
