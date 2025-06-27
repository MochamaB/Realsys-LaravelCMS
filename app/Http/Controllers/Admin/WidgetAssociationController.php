<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Widget;
use App\Models\ContentType;
use App\Models\WidgetContentTypeAssociation;
use App\Services\WidgetContentCompatibilityService;
use App\Services\WidgetContentAssociationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WidgetAssociationController extends Controller
{
    protected $compatibilityService;
    protected $associationService;

    /**
     * Constructor with dependency injection
     */
    public function __construct(
        WidgetContentCompatibilityService $compatibilityService,
        WidgetContentAssociationService $associationService
    ) {
        $this->compatibilityService = $compatibilityService;
        $this->associationService = $associationService;
    }

    /**
     * Store a new widget content type association
     *
     * @param Request $request
     * @param Widget $widget
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request, Widget $widget)
    {
        $request->validate([
            'content_type_id' => 'required|exists:content_types,id',
            'auto_map' => 'nullable|boolean'
        ]);

        try {
            // Get the content type
            $contentType = ContentType::findOrFail($request->content_type_id);
            
            // Check compatibility
            $compatibility = $this->compatibilityService->checkCompatibility($widget, $contentType);
            
            if (!$compatibility['compatible']) {
                return redirect()->route('admin.widgets.show', $widget)
                    ->with('error', 'Widget is not compatible with this content type: ' . $compatibility['message']);
            }
            
            // Get field mappings
            $fieldMappings = [];
            
            if ($request->boolean('auto_map', true)) {
                // Auto-generate mappings
                $fieldMappings = $this->compatibilityService->generateFieldMappings($widget, $contentType);
            } elseif ($request->has('field_mappings') && is_array($request->field_mappings)) {
                // Use provided mappings
                $fieldMappings = $request->field_mappings;
            }
            
            // Additional options
            $options = $request->only(['limit', 'sort_field', 'sort_direction', 'filters', 'searchable_fields']);
            
            // Create the association
            $association = $this->associationService->createAssociation($widget, $contentType, $fieldMappings, $options);
            
            return redirect()->route('admin.widgets.show', $widget)
                ->with('success', "Widget successfully associated with {$contentType->name} content type.");
            
        } catch (\Exception $e) {
            Log::error('Failed to associate widget with content type: ' . $e->getMessage(), [
                'widget_id' => $widget->id,
                'content_type_id' => $request->content_type_id
            ]);
            
            return redirect()->route('admin.widgets.show', $widget)
                ->with('error', 'Failed to associate widget with content type: ' . $e->getMessage());
        }
    }
    
    /**
     * Update an existing widget content type association
     *
     * @param Request $request
     * @param WidgetContentTypeAssociation $association
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, WidgetContentTypeAssociation $association)
    {
        try {
            // Validate and get mappings
            $request->validate([
                'mappings' => 'required|array',
            ]);
            
            $fieldMappings = $request->mappings;
            
            // Additional options
            $options = $request->only(['limit', 'sort_field', 'sort_direction', 'filters', 'searchable_fields']);
            
            // Update the association
            $this->associationService->updateMappings($association, $fieldMappings, $options);
            
            return redirect()->route('admin.widgets.show', $association->widget_id)
                ->with('success', 'Field mappings updated successfully.');
                
        } catch (\Exception $e) {
            Log::error('Failed to update widget association: ' . $e->getMessage(), [
                'association_id' => $association->id
            ]);
            
            return redirect()->route('admin.widgets.show', $association->widget_id)
                ->with('error', 'Failed to update field mappings: ' . $e->getMessage());
        }
    }
    
    /**
     * Toggle the active status of a widget content type association
     *
     * @param Request $request
     * @param WidgetContentTypeAssociation $association
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggle(Request $request, WidgetContentTypeAssociation $association)
    {
        try {
            $request->validate([
                'is_active' => 'required|boolean'
            ]);
            
            $isActive = $request->boolean('is_active');
            
            if ($isActive) {
                // If activating, make this the exclusive active association
                $this->associationService->setActiveAssociation(
                    $association->widget, 
                    $association->contentType,
                    true // exclusive
                );
            } else {
                // Just update this association's status
                $association->update(['is_active' => false]);
            }
            
            return response()->json([
                'success' => true,
                'message' => "Association with {$association->contentType->name} " . 
                    ($isActive ? 'activated' : 'deactivated'),
                'is_active' => $isActive
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to toggle widget association status: ' . $e->getMessage(), [
                'association_id' => $association->id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to toggle association status: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Delete a widget content type association
     *
     * @param WidgetContentTypeAssociation $association
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(WidgetContentTypeAssociation $association)
    {
        try {
            $widgetId = $association->widget_id;
            $contentTypeName = $association->contentType->name ?? 'Unknown';
            
            $this->associationService->removeAssociation(
                $association->widget, 
                $association->contentType
            );
            
            return redirect()->route('admin.widgets.show', $widgetId)
                ->with('success', "Association with {$contentTypeName} content type removed successfully.");
                
        } catch (\Exception $e) {
            Log::error('Failed to delete widget association: ' . $e->getMessage(), [
                'association_id' => $association->id
            ]);
            
            return redirect()->route('admin.widgets.show', $association->widget_id)
                ->with('error', 'Failed to remove content type association: ' . $e->getMessage());
        }
    }
}
