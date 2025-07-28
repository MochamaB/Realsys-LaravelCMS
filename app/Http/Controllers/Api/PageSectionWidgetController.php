<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PageSection;
use App\Models\PageSectionWidget;
use App\Models\Widget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PageSectionWidgetController extends Controller
{
    /**
     * Store a newly created widget in storage.
     */
    public function store(Request $request)
    {
        try {
            Log::info('Creating PageSectionWidget', [
                'request_data' => $request->all()
            ]);

            // Validate the request
            $validator = Validator::make($request->all(), [
                'page_section_id' => 'required|integer|exists:page_sections,id',
                'widget_id' => 'required|integer|exists:widgets,id',
                'position' => 'nullable|integer',
                'grid_x' => 'required|integer|min:0',
                'grid_y' => 'required|integer|min:0',
                'grid_w' => 'required|integer|min:1',
                'grid_h' => 'required|integer|min:1',
                'grid_id' => 'required|string|unique:page_section_widgets,grid_id',
                'column_position' => 'nullable|integer',
                'settings' => 'nullable|array',
                'content_query' => 'nullable|array',
                'css_classes' => 'nullable|string',
                'padding' => 'nullable|array',
                'margin' => 'nullable|array',
                'min_height' => 'nullable|integer',
                'max_height' => 'nullable|integer',
            ]);

            if ($validator->fails()) {
                Log::error('Validation failed for PageSectionWidget creation', [
                    'errors' => $validator->errors()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();

            // Get the page section
            $pageSection = PageSection::findOrFail($validated['page_section_id']);
            
            // Get the widget
            $widget = Widget::findOrFail($validated['widget_id']);

            // Calculate position if not provided
            if (!isset($validated['position'])) {
                $validated['position'] = $pageSection->widgets()->max('position') + 1;
            }

            // Create the page section widget
            $pageSectionWidget = PageSectionWidget::create($validated);

            // Load relationships for response
            $pageSectionWidget->load(['widget', 'pageSection']);

            Log::info('PageSectionWidget created successfully', [
                'page_section_widget_id' => $pageSectionWidget->id,
                'page_section_id' => $pageSectionWidget->page_section_id,
                'widget_id' => $pageSectionWidget->widget_id,
                'grid_id' => $pageSectionWidget->grid_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Widget added to section successfully',
                'data' => $pageSectionWidget
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating PageSectionWidget: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'exception' => $e
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create widget',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified widget.
     */
    public function show(PageSectionWidget $pageSectionWidget)
    {
        try {
            $pageSectionWidget->load(['widget', 'pageSection']);

            return response()->json([
                'success' => true,
                'data' => $pageSectionWidget
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving PageSectionWidget: ' . $e->getMessage(), [
                'page_section_widget_id' => $pageSectionWidget->id ?? null,
                'exception' => $e
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve widget',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified widget in storage.
     */
    public function update(Request $request, PageSectionWidget $pageSectionWidget)
    {
        try {
            Log::info('Updating PageSectionWidget', [
                'page_section_widget_id' => $pageSectionWidget->id,
                'request_data' => $request->all()
            ]);

            // Validate the request
            $validator = Validator::make($request->all(), [
                'position' => 'nullable|integer',
                'grid_x' => 'nullable|integer|min:0',
                'grid_y' => 'nullable|integer|min:0',
                'grid_w' => 'nullable|integer|min:1',
                'grid_h' => 'nullable|integer|min:1',
                'column_position' => 'nullable|integer',
                'settings' => 'nullable|array',
                'content_query' => 'nullable|array',
                'css_classes' => 'nullable|string',
                'padding' => 'nullable|array',
                'margin' => 'nullable|array',
                'min_height' => 'nullable|integer',
                'max_height' => 'nullable|integer',
            ]);

            if ($validator->fails()) {
                Log::error('Validation failed for PageSectionWidget update', [
                    'errors' => $validator->errors()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $validated = $validator->validated();

            // Update the widget
            $pageSectionWidget->update($validated);

            // Load relationships for response
            $pageSectionWidget->load(['widget', 'pageSection']);

            Log::info('PageSectionWidget updated successfully', [
                'page_section_widget_id' => $pageSectionWidget->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Widget updated successfully',
                'data' => $pageSectionWidget
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating PageSectionWidget: ' . $e->getMessage(), [
                'page_section_widget_id' => $pageSectionWidget->id,
                'request_data' => $request->all(),
                'exception' => $e
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update widget',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified widget from storage.
     */
    public function destroy(PageSectionWidget $pageSectionWidget)
    {
        try {
            Log::info('Deleting PageSectionWidget', [
                'page_section_widget_id' => $pageSectionWidget->id,
                'page_section_id' => $pageSectionWidget->page_section_id,
                'widget_id' => $pageSectionWidget->widget_id
            ]);

            $pageSectionWidget->delete();

            Log::info('PageSectionWidget deleted successfully', [
                'page_section_widget_id' => $pageSectionWidget->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Widget deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting PageSectionWidget: ' . $e->getMessage(), [
                'page_section_widget_id' => $pageSectionWidget->id,
                'exception' => $e
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete widget',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 