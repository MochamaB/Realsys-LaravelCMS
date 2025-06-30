<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Models\MediaFolder;
use App\Models\MediaTag;

class MediaPicker extends Component
{
    /**
     * The field name to store selected media ID(s)
     */
    public $name;
    
    /**
     * Whether multiple media items can be selected
     */
    public $multiple;
    
    /**
     * Label for the field
     */
    public $label;
    
    /**
     * Pre-selected media IDs
     */
    public $selected;
    
    /**
     * Allow only specific media types (image, video, document, etc.)
     */
    public $allowedTypes;
    
    /**
     * Create a new component instance.
     *
     * @param string $name
     * @param string $label
     * @param bool $multiple
     * @param array|null $selected
     * @param array|null $allowedTypes
     * @return void
     */
    public function __construct(
        $name, 
        $label = 'Media', 
        $multiple = false, 
        $selected = null, 
        $allowedTypes = null
    ) {
        $this->name = $name;
        $this->label = $label;
        $this->multiple = $multiple;
        $this->selected = $selected ? (is_array($selected) ? $selected : [$selected]) : [];
        $this->allowedTypes = $allowedTypes;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        $folders = MediaFolder::whereNull('parent_id')
            ->with('allChildren')
            ->orderBy('name')
            ->get();
            
        $tags = MediaTag::orderBy('name')->get();
        
        return view('components.media-picker', [
            'folders' => $folders,
            'tags' => $tags,
        ]);
    }
}
