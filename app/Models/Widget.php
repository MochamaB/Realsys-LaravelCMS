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
        'slug',
        'description',
        'icon',
        'view_path'
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
   

    /**
     * Get the page sections that use this widget.
     */
    public function pageSections()
    {
        return $this->belongsToMany(PageSection::class, 'page_section_widgets')
            ->withPivot('position', 'settings', 'content_query')
            ->withTimestamps()
            ->orderBy('page_section_widgets.position');
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
            'slug' => $this->slug,
            'theme' => $this->theme ? $this->theme->name : null,
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
        $viewData = [
            'widget' => $this,
            'data' => $this->getData()
        ];
        
        // If the widget is attached to a page section with a content query
        if (request()->route('page_section_widget')) {
            $pageSectionWidget = request()->route('page_section_widget');
            
            // If there's a content query in the pivot
            if ($pageSectionWidget && !empty($pageSectionWidget->content_query)) {
                // In the future, we'll need to implement a content query executor here
                // For now, just pass the content query to the view
                $viewData['content_query'] = $pageSectionWidget->content_query;
            }
            
            // Add settings from the pivot
            if ($pageSectionWidget && !empty($pageSectionWidget->settings)) {
                $viewData['settings'] = $pageSectionWidget->settings;
            }
        }
        
        // Use widget's view_path for rendering
        $viewPath = $this->view_path ?: 'widgets.' . $this->slug;
        return view($viewPath, $viewData)->render();
    }
}
