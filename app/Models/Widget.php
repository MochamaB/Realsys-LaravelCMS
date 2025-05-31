<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Widget extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'theme_id',
        'name',
        'identifier',
        'description',
        'settings'
    ];

    protected $casts = [
        'settings' => 'json'
    ];

    /**
     * Get the theme that owns the widget.
     */
    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class);
    }
    
    /**
     * Get the field definitions for this widget.
     */
    public function fieldDefinitions(): HasMany
    {
        return $this->hasMany(WidgetFieldDefinition::class);
    }
    
    /**
     * Get the content type associations for this widget.
     */
    public function contentTypeAssociations(): HasMany
    {
        return $this->hasMany(WidgetContentTypeAssociation::class);
    }
    
    /**
     * Get the content types associated with this widget.
     */
    public function contentTypes()
    {
        return $this->belongsToMany(ContentType::class, 'widget_content_type_associations')
            ->withTimestamps();
    }
    
    /**
     * Get the display settings associated with this widget.
     */
    public function displaySettings()
    {
        return $this->belongsTo(WidgetDisplaySetting::class, 'display_settings_id');
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
        $data = [
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'type' => $this->widgetType ? $this->widgetType->name : null,
            'type_key' => $this->widgetType ? $this->widgetType->slug : null,
        ];
        
        // Add content query data if available
        if ($this->contentQuery) {
            $data['content_query'] = [
                'content_type' => $this->contentQuery->contentType ? $this->contentQuery->contentType->name : null,
                'limit' => $this->contentQuery->limit,
                'order_by' => $this->contentQuery->order_by,
                'order_direction' => $this->contentQuery->order_direction,
            ];
        }
        
        // Add display settings if available
        if ($this->displaySettings) {
            $data['display_settings'] = [
                'layout' => $this->displaySettings->layout,
                'view_mode' => $this->displaySettings->view_mode,
                'pagination_type' => $this->displaySettings->pagination_type,
                'items_per_page' => $this->displaySettings->items_per_page,
            ];
        }
        
        return $data;
    }

    /**
     * Render the widget using its type's view.
     */
    public function render()
    {
        $widgetType = $this->widgetType;
        $viewData = [
            'widget' => $this,
            'data' => $this->getData()
        ];
        
        // If this widget uses the content query system
        if ($this->content_query_id) {
            // Get content items using the query
            $contentQuery = $this->contentQuery;
            if ($contentQuery) {
                $contentItems = $contentQuery->executeQuery();
                $viewData['contentItems'] = $contentItems;
            }
            
            // Get display settings
            $displaySettings = $this->displaySettings;
            if ($displaySettings) {
                $viewData['displaySettings'] = $displaySettings;
                
                // If display settings specify a view path, use it
                if ($displaySettings->layout) {
                    $viewPath = $displaySettings->getViewPath();
                    return view($viewPath, $viewData)->render();
                }
            }
        }
        
        // Fallback to traditional widget rendering
        $viewPath = 'widgets.' . $widgetType->slug;
        return view($viewPath, $viewData)->render();
    }
}
