<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Widget;
use App\Models\ContentType;
use Illuminate\Http\Request;

class WidgetContentTypeController extends Controller
{
    /**
     * Store a newly created content type association.
     */
    public function store(Request $request, Widget $widget)
    {
        $validated = $request->validate([
            'content_type_id' => 'required|exists:content_types,id'
        ]);
        
        $contentType = ContentType::findOrFail($validated['content_type_id']);
        
        // Create the association if it doesn't exist
        if (!$widget->contentTypes()->where('content_type_id', $contentType->id)->exists()) {
            $widget->contentTypes()->attach($contentType->id);
            return redirect()->back()->with('success', "Content type '{$contentType->name}' associated successfully.");
        }
        
        return redirect()->back()->with('info', "Content type '{$contentType->name}' is already associated with this widget.");
    }

    /**
     * Remove the specified content type association.
     */
    public function destroy(Widget $widget, ContentType $contentType)
    {
        $widget->contentTypes()->detach($contentType->id);
        
        return redirect()->back()->with('success', "Content type '{$contentType->name}' disassociated successfully.");
    }
}