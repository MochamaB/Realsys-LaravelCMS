<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageSection;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PageSectionController extends Controller
{
    /**
     * List all sections for a page (with template section info).
     */
    public function index(Page $page)
    {
        $sections = $page->sections()->with('templateSection')->orderBy('position')->get();
        return response()->json(['sections' => $sections]);
    }

    /**
     * Create a new section for a page.
     */
    public function store(Request $request, Page $page)
    {
        $validated = $request->validate([
            'template_section_id' => 'required|exists:template_sections,id',
            'name' => 'required|string|max:255',
            'identifier' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                'unique:page_sections,identifier'
            ],
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);
        if (empty($validated['identifier'])) {
            $validated['identifier'] = Str::slug($validated['name']);
        }
        $validated['order_index'] = $page->sections()->max('order_index') + 1;
        $section = $page->sections()->create($validated);
        return response()->json(['success' => true, 'section' => $section]);
    }

    /**
     * Update a section for a page.
     */
    public function update(Request $request, Page $page, PageSection $section)
    {
        $validated = $request->validate([
            'template_section_id' => 'sometimes|exists:template_sections,id',
            'name' => 'sometimes|string|max:255',
            'identifier' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                'unique:page_sections,identifier,' . $section->id
            ],
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'css_classes' => 'nullable|string|max:255',
            'background_color' => 'nullable|string|max:50',
            'padding' => 'nullable|string|max:50',
            'margin' => 'nullable|string|max:50',
            'column_span_override' => 'nullable|integer',
            'column_offset_override' => 'nullable|integer',
            'position' => 'nullable|integer',
        ]);
        $section->update($validated);
        return response()->json(['success' => true, 'section' => $section]);
    }

    /**
     * Delete a section from a page.
     */
    public function destroy(Page $page, PageSection $section)
    {
        $section->delete();
        return response()->json(['success' => true]);
    }

    /**
     * Reorder sections for a page.
     */
    public function reorder(Request $request, Page $page)
    {
        $validated = $request->validate([
            'sections' => 'required|array',
            'sections.*.id' => 'required|exists:page_sections,id',
            'sections.*.order' => 'required|integer|min:0'
        ]);
        foreach ($validated['sections'] as $item) {
            PageSection::where('id', $item['id'])->update([
                'order_index' => $item['order']
            ]);
        }
        return response()->json(['success' => true]);
    }
}
