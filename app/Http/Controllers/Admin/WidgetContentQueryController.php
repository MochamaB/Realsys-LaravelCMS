<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContentType;
use App\Models\Widget;
use App\Models\WidgetContentQuery;
use Illuminate\Http\Request;

class WidgetContentQueryController extends Controller
{
    /**
     * Display a listing of the widget content queries.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $queries = WidgetContentQuery::with('contentType', 'filters')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        return view('admin.widget_content_queries.index', compact('queries'));
    }

    /**
     * Show the form for creating a new widget content query.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $contentTypes = ContentType::where('is_active', true)
            ->orderBy('name')
            ->get();
            
        $orderOptions = [
            'created_at' => 'Created Date',
            'updated_at' => 'Updated Date',
            'title' => 'Title',
            'status' => 'Status'
        ];
        
        $directionOptions = [
            'asc' => 'Ascending',
            'desc' => 'Descending'
        ];
        
        return view('admin.widget_content_queries.create', compact(
            'contentTypes',
            'orderOptions',
            'directionOptions'
        ));
    }

    /**
     * Store a newly created widget content query in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'content_type_id' => 'nullable|exists:content_types,id',
            'limit' => 'nullable|integer|min:1',
            'offset' => 'nullable|integer|min:0',
            'order_by' => 'nullable|string',
            'order_direction' => 'required|in:asc,desc',
        ]);
        
        $query = WidgetContentQuery::create($validated);
        
        return redirect()
            ->route('admin.widget-content-queries.edit', $query)
            ->with('success', 'Content query created successfully.');
    }

    /**
     * Display the specified widget content query.
     *
     * @param  \App\Models\WidgetContentQuery  $contentQuery
     * @return \Illuminate\Http\Response
     */
    public function show(WidgetContentQuery $contentQuery)
    {
        $contentQuery->load('contentType', 'filters', 'widgets');
        
        // Preview the results of this query
        $contentItems = $contentQuery->executeQuery();
        
        return view('admin.widget_content_queries.show', compact('contentQuery', 'contentItems'));
    }

    /**
     * Show the form for editing the specified widget content query.
     *
     * @param  \App\Models\WidgetContentQuery  $contentQuery
     * @return \Illuminate\Http\Response
     */
    public function edit(WidgetContentQuery $contentQuery)
    {
        $contentQuery->load('contentType', 'filters');
        
        $contentTypes = ContentType::where('is_active', true)
            ->orderBy('name')
            ->get();
            
        $orderOptions = [
            'created_at' => 'Created Date',
            'updated_at' => 'Updated Date',
            'title' => 'Title',
            'status' => 'Status'
        ];
        
        $directionOptions = [
            'asc' => 'Ascending',
            'desc' => 'Descending'
        ];
        
        // Get content type fields for filter creation
        $contentTypeFields = [];
        if ($contentQuery->contentType) {
            $contentTypeFields = $contentQuery->contentType->fields()->get();
        }
        
        return view('admin.widget_content_queries.edit', compact(
            'contentQuery',
            'contentTypes',
            'contentTypeFields',
            'orderOptions',
            'directionOptions'
        ));
    }

    /**
     * Update the specified widget content query in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\WidgetContentQuery  $contentQuery
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, WidgetContentQuery $contentQuery)
    {
        $validated = $request->validate([
            'content_type_id' => 'nullable|exists:content_types,id',
            'limit' => 'nullable|integer|min:1',
            'offset' => 'nullable|integer|min:0',
            'order_by' => 'nullable|string',
            'order_direction' => 'required|in:asc,desc',
        ]);
        
        $contentQuery->update($validated);
        
        return redirect()
            ->route('admin.widget-content-queries.edit', $contentQuery)
            ->with('success', 'Content query updated successfully.');
    }

    /**
     * Remove the specified widget content query from storage.
     *
     * @param  \App\Models\WidgetContentQuery  $contentQuery
     * @return \Illuminate\Http\Response
     */
    public function destroy(WidgetContentQuery $contentQuery)
    {
        // Check if any widgets are using this query
        $widgetsUsingQuery = Widget::where('content_query_id', $contentQuery->id)->count();
        
        if ($widgetsUsingQuery > 0) {
            return redirect()
                ->route('admin.widget-content-queries.index')
                ->with('error', "Cannot delete query because it's being used by {$widgetsUsingQuery} widgets.");
        }
        
        $contentQuery->filters()->delete();
        $contentQuery->delete();
        
        return redirect()
            ->route('admin.widget-content-queries.index')
            ->with('success', 'Content query deleted successfully.');
    }
    
    /**
     * Preview the results of a content query.
     *
     * @param  \App\Models\WidgetContentQuery  $contentQuery
     * @return \Illuminate\Http\Response
     */
    public function preview(WidgetContentQuery $contentQuery)
    {
        $contentItems = $contentQuery->executeQuery();
        
        return view('admin.widget_content_queries.preview', compact('contentQuery', 'contentItems'));
    }
}
