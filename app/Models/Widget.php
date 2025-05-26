<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Widget extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'widget_type_id',
        'name',
        'description',
        'is_active',
        'status',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'status' => 'string'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * Get the widget type that owns the widget.
     */
    public function widgetType()
    {
        return $this->belongsTo(WidgetType::class);
    }

    /**
     * Get the page section that owns the widget.
     */
    public function pageSections()
    {
        return $this->belongsToMany(PageSection::class, 'page_widgets')
            ->withPivot('order_index')
            ->withTimestamps()
            ->orderBy('page_widgets.order_index');
    }

    /**
     * Get the field values for the widget.
     */
    public function fieldValues()
    {
        return $this->hasMany(WidgetFieldValue::class);
    }

    /**
     * Get the user that created the widget.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the admin that created the widget.
     */
    public function adminCreator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    /**
     * Get the user that last updated the widget.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the admin that last updated the widget.
     */
    public function adminUpdater()
    {
        return $this->belongsTo(Admin::class, 'updated_by');
    }

    /**
     * Get the creator regardless of whether it's a user or admin.
     * This method determines the creator type based on the guard.
     */
    public function getCreator()
    {
        // Check if the creator is an admin
        if ($admin = $this->adminCreator()->first()) {
            return $admin;
        }

        // Otherwise return the user
        return $this->creator()->first();
    }

    /**
     * Get the updater regardless of whether it's a user or admin.
     * This method determines the updater type based on the guard.
     */
    public function getUpdater()
    {
        // Check if the updater is an admin
        if ($admin = $this->adminUpdater()->first()) {
            return $admin;
        }

        // Otherwise return the user
        return $this->updater()->first();
    }

    /**
     * Get all the data for this widget in a structured format.
     */
    public function getData()
    {
        $data = [];
        
        // Get all field values
        $fieldValues = $this->fieldValues()->with('field')->get();
        
        foreach ($fieldValues as $fieldValue) {
            $field = $fieldValue->field;
            $key = $field->key;
            $value = $fieldValue->value;
            
            // Cast the value based on field type
            switch ($field->type) {
                case 'boolean':
                    $value = (bool) $value;
                    break;
                case 'integer':
                    $value = (int) $value;
                    break;
                case 'float':
                    $value = (float) $value;
                    break;
                case 'json':
                    $value = json_decode($value, true);
                    break;
            }
            
            $data[$key] = $value;
        }
        
        return $data;
    }

    /**
     * Render the widget using its type's view.
     */
    public function render()
    {
        $widgetType = $this->widgetType;
        $viewPath = 'widgets.' . $widgetType->slug;
        
        return view($viewPath, [
            'widget' => $this,
            'data' => $this->getData()
        ])->render();
    }
}
