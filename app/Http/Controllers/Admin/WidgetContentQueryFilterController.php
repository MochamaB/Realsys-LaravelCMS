<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentTypeField;
use App\Models\WidgetContentQuery;
use App\Models\WidgetContentQueryFilter;
use Illuminate\Http\Request;

class WidgetContentQueryFilterController extends Controller
{
    /**
     * Store a newly created filter in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\WidgetContentQuery  $contentQuery
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, WidgetContentQuery $contentQuery)
    {
        $validated = $request->validate([
            'field_id' => 'nullable|exists:content_type_fields,id',
            'field_key' => 'nullable|string',
            'operator' => 'required|string',
            'value' => 'nullable|string',
            'condition_group' => 'nullable|string',
        ]);
        
        // Ensure either field_id or field_key is set, but not both
        if (empty($validated['field_id']) && empty($validated['field_key'])) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Either a field or property must be selected.');
        }
        
        $validated['query_id'] = $contentQuery->id;
        
        $filter = WidgetContentQueryFilter::create($validated);
        
        return redirect()
            ->route('admin.widget-content-queries.edit', $contentQuery)
            ->with('success', 'Filter added successfully.');
    }

    /**
     * Show the form for editing the specified filter.
     *
     * @param  \App\Models\WidgetContentQueryFilter  $filter
     * @return \Illuminate\Http\Response
     */
    public function edit(WidgetContentQueryFilter $filter)
    {
        $contentQuery = $filter->query;
        $contentTypeFields = [];
        
        if ($contentQuery->contentType) {
            $contentTypeFields = $contentQuery->contentType->fields()->get();
        }
        
        $operators = [
            'equals' => 'Equals',
            'not_equals' => 'Not Equals',
            'contains' => 'Contains',
            'starts_with' => 'Starts With',
            'ends_with' => 'Ends With',
            'greater_than' => 'Greater Than',
            'less_than' => 'Less Than',
            'in' => 'In List',
            'not_in' => 'Not In List',
            'is_null' => 'Is Empty',
            'is_not_null' => 'Is Not Empty'
        ];
        
        $propertyFields = [
            'title' => 'Title',
            'slug' => 'Slug',
            'status' => 'Status',
            'created_at' => 'Created Date',
            'updated_at' => 'Updated Date',
            'published_at' => 'Published Date'
        ];
        
        return view('admin.widget_content_query_filters.edit', compact(
            'filter',
            'contentQuery',
            'contentTypeFields',
            'operators',
            'propertyFields'
        ));
    }

    /**
     * Update the specified filter in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\WidgetContentQueryFilter  $filter
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, WidgetContentQueryFilter $filter)
    {
        $validated = $request->validate([
            'field_id' => 'nullable|exists:content_type_fields,id',
            'field_key' => 'nullable|string',
            'operator' => 'required|string',
            'value' => 'nullable|string',
            'condition_group' => 'nullable|string',
        ]);
        
        // Ensure either field_id or field_key is set, but not both
        if (empty($validated['field_id']) && empty($validated['field_key'])) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Either a field or property must be selected.');
        }
        
        // If field_id is set, clear field_key and vice versa
        if (!empty($validated['field_id'])) {
            $validated['field_key'] = null;
        } elseif (!empty($validated['field_key'])) {
            $validated['field_id'] = null;
        }
        
        $filter->update($validated);
        
        $contentQuery = $filter->query;
        
        return redirect()
            ->route('admin.widget-content-queries.edit', $contentQuery)
            ->with('success', 'Filter updated successfully.');
    }

    /**
     * Remove the specified filter from storage.
     *
     * @param  \App\Models\WidgetContentQueryFilter  $filter
     * @return \Illuminate\Http\Response
     */
    public function destroy(WidgetContentQueryFilter $filter)
    {
        $contentQuery = $filter->query;
        $filter->delete();
        
        return redirect()
            ->route('admin.widget-content-queries.edit', $contentQuery)
            ->with('success', 'Filter deleted successfully.');
    }
    
    /**
     * Get fields for a content type via AJAX.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getContentTypeFields(Request $request)
    {
        $contentTypeId = $request->input('content_type_id');
        
        if (!$contentTypeId) {
            return response()->json(['fields' => []]);
        }
        
        $fields = ContentTypeField::where('content_type_id', $contentTypeId)
            ->orderBy('order_index')
            ->get(['id', 'name', 'key', 'type']);
            
        return response()->json(['fields' => $fields]);
    }
}
