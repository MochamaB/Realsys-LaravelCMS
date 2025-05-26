<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageSection;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PageSectionController extends Controller
{
    /**
     * Display a listing of the sections for a page.
     */
    public function index(Page $page)
    {
        $sections = $page->sections()->with('templateSection')->orderBy('order_index')->get();
        
        return view('admin.pages.sections.index', compact('page', 'sections'));
    }

    /**
     * Show the form for creating a new section.
     */
    public function create(Page $page)
    {
        $templateSections = $page->template->sections;
        
        return view('admin.pages.sections.create', compact('page', 'templateSections'));
    }

    /**
     * Store a newly created section in storage.
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

        // Generate identifier if not provided
        if (empty($validated['identifier'])) {
            $validated['identifier'] = Str::slug($validated['name']);
        }

        // Set order index
        $validated['order_index'] = $page->sections()->max('order_index') + 1;
        
        // Create section
        $section = $page->sections()->create($validated);

        return redirect()
            ->route('admin.pages.sections.index', $page)
            ->with('success', 'Section created successfully.');
    }

    /**
     * Show the form for editing the specified section.
     */
    public function edit(Page $page, PageSection $section)
    {
        $templateSections = $page->template->sections;
        
        return view('admin.pages.sections.edit', compact('page', 'section', 'templateSections'));
    }

    /**
     * Update the specified section in storage.
     */
    public function update(Request $request, Page $page, PageSection $section)
    {
        $validated = $request->validate([
            'template_section_id' => 'required|exists:template_sections,id',
            'name' => 'required|string|max:255',
            'identifier' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                'unique:page_sections,identifier,' . $section->id
            ],
            'description' => 'nullable|string',
            'is_active' => 'boolean'
        ]);

        // Generate identifier if not provided
        if (empty($validated['identifier'])) {
            $validated['identifier'] = Str::slug($validated['name']);
        }

        // Update section
        $section->update($validated);

        return redirect()
            ->route('admin.pages.sections.index', $page)
            ->with('success', 'Section updated successfully.');
    }

    /**
     * Remove the specified section from storage.
     */
    public function destroy(Page $page, PageSection $section)
    {
        $section->delete();

        return redirect()
            ->route('admin.pages.sections.index', $page)
            ->with('success', 'Section deleted successfully.');
    }

    /**
     * Toggle the section's active status.
     */
    public function toggle(Page $page, PageSection $section)
    {
        $section->update([
            'is_active' => !$section->is_active
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Section status updated successfully.'
        ]);
    }

    /**
     * Reorder sections.
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

        return response()->json([
            'success' => true,
            'message' => 'Sections reordered successfully.'
        ]);
    }
}
