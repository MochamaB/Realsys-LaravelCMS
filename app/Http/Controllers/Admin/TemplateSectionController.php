<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Template;
use App\Models\TemplateSection;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TemplateSectionController extends Controller
{
    /**
     * Display a listing of the template's sections.
     */
    public function index(Template $template)
    {
        $template->load(['sections' => function($query) {
            $query->orderBy('position');
        }]);
        
        $sectionTypes = [
            'full-width' => 'Full Width Section',
            'multi-column' => 'Multi-Column Section',
            'sidebar-left' => 'Sidebar Left Section',
            'sidebar-right' => 'Sidebar Right Section',
        ];
        
        $columnLayouts = [
            '12' => 'Full Width (12)',
            '6-6' => 'Two Equal Columns (6-6)',
            '4-4-4' => 'Three Equal Columns (4-4-4)',
            '3-3-3-3' => 'Four Equal Columns (3-3-3-3)',
            '8-4' => 'Wide & Narrow (8-4)',
            '4-8' => 'Narrow & Wide (4-8)',
            '3-6-3' => 'Sidebar, Main, Sidebar (3-6-3)',
        ];
        
        // Get empty section for the form
        $newSection = new TemplateSection();
        $newSection->section_type = 'full-width';
        
        return view('admin.templates.sections.index', compact('template', 'sectionTypes', 'columnLayouts', 'newSection'));
    }

    /**
     * Show the form for creating a new section.
     */
    public function create(Template $template)
    {
        $sectionTypes = TemplateSection::getTypes();
        $columnLayouts = TemplateSection::getColumnLayouts();
        $nextPosition = $template->sections()->max('position') + 1;
        
        // Get empty section for the form
        $newSection = new TemplateSection();
        $newSection->section_type = 'full-width';
        $newSection->position = $nextPosition;
        
        return view('admin.templates.sections.create', compact('template', 'sectionTypes', 'columnLayouts', 'newSection'));
    }
    
    /**
     * Show the form for editing the specified section.
     */
    public function edit(Template $template, TemplateSection $section)
    {
        // Ensure the section belongs to the template
        if ($section->template_id !== $template->id) {
            abort(404);
        }
        
        $sectionTypes = TemplateSection::getTypes();
        $columnLayouts = TemplateSection::getColumnLayouts();
        
        return view('admin.templates.sections.edit', compact('template', 'section', 'sectionTypes', 'columnLayouts'));
    }

    /**
     * Store a newly created section in storage.
     */
    public function store(Request $request, Template $template)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'section_type' => [
                'required',
                Rule::in(['full-width', 'multi-column', 'sidebar-left', 'sidebar-right'])
            ],
            'column_layout' => 'nullable|string|max:50',
            'is_repeatable' => 'boolean',
            'max_widgets' => 'nullable|integer|min:1',
            'position' => 'nullable|integer|min:0',
        ]);
        
        // Generate slug from name
        $slug = Str::slug($validated['name']);
        
        // Make sure slug is unique for this template
        $count = 0;
        $originalSlug = $slug;
        while (TemplateSection::where('template_id', $template->id)
                              ->where('slug', $slug)
                              ->exists()) {
            $count++;
            $slug = $originalSlug . '-' . $count;
        }
        
        // Set position if not provided
        if (!isset($validated['position'])) {
            $validated['position'] = $template->sections()->max('position') + 1;
        }
        
        // Set default column layout if needed
        if (!isset($validated['column_layout']) || empty($validated['column_layout'])) {
            $validated['column_layout'] = '12'; // Default to full width
        }
        
        // Create the section
        $section = TemplateSection::create([
            'template_id' => $template->id,
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'section_type' => $validated['section_type'],
            'column_layout' => $validated['column_layout'],
            'is_repeatable' => $request->has('is_repeatable') && $request->is_repeatable ? true : false,
            'max_widgets' => $validated['max_widgets'] ?? null,
            'position' => $validated['position'],
        ]);
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Section created successfully.',
                'section' => $section->load('template')
            ]);
        }
        
        return redirect()->route('admin.templates.sections.index', $template)
                ->with('success', 'Section created successfully.');
    }

    /**
     * Display the specified section.
     */
    public function show(Template $template, TemplateSection $section)
    {
        // Ensure the section belongs to the template
        if ($section->template_id !== $template->id) {
            abort(404);
        }
        
        $section->load('template');
        $sectionTypes = TemplateSection::getTypes();
        
        return view('admin.template_sections.show', compact('template', 'section', 'sectionTypes'));
    }

    /**
     * Get a specific section for editing via AJAX
     */
    public function getSection(Request $request, Template $template, TemplateSection $section)
    {
        // Ensure the section belongs to the template
        if ($section->template_id !== $template->id) {
            return response()->json(['success' => false, 'message' => 'Section not found'], 404);
        }
        
        return response()->json([
            'success' => true,
            'section' => $section
        ]);
    }

    /**
     * Update the specified section in storage.
     */
    public function update(Request $request, Template $template, TemplateSection $section)
    {
        // Ensure the section belongs to the template
        if ($section->template_id !== $template->id) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Section not found'], 404);
            }
            abort(404);
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'section_type' => [
                'required',
                Rule::in(['full-width', 'multi-column', 'sidebar-left', 'sidebar-right'])
            ],
            'column_layout' => 'nullable|string|max:50',
            'is_repeatable' => 'boolean',
            'max_widgets' => 'nullable|integer|min:1',
            'position' => 'nullable|integer|min:0',
        ]);
        
        // Set default column layout if needed
        if (!isset($validated['column_layout']) || empty($validated['column_layout'])) {
            $validated['column_layout'] = '12'; // Default to full width
        }
        
        // Update the section
        $section->name = $validated['name'];
        $section->description = $validated['description'] ?? null;
        $section->section_type = $validated['section_type'];
        $section->column_layout = $validated['column_layout'];
        $section->is_repeatable = $request->has('is_repeatable') && $request->is_repeatable ? true : false;
        $section->max_widgets = $validated['max_widgets'] ?? null;
        $section->position = $validated['position'] ?? $section->position;
        $section->save();
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Section updated successfully.',
                'section' => $section->fresh()
            ]);
        }
        
        return redirect()->route('admin.templates.sections.index', $template)
                ->with('success', 'Section updated successfully.');
    }

    /**
     * Update the order of sections.
     */
    public function updatePositions(Request $request, Template $template)
    {
        $validated = $request->validate([
            'positions' => 'required|array',
            'positions.*' => 'required|exists:template_sections,id',
        ]);
        
        // Update positions based on array order
        foreach ($validated['positions'] as $index => $sectionId) {
            TemplateSection::where('id', $sectionId)
                ->where('template_id', $template->id)
                ->update(['position' => $index]);
        }
        
        return response()->json([
            'success' => true, 
            'message' => 'Section positions updated successfully'
        ]);
    }

    /**
     * Remove the specified section from storage.
     */
    public function destroy(Request $request, Template $template, TemplateSection $section)
    {
        // Ensure the section belongs to the template
        if ($section->template_id !== $template->id) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Section not found'], 404);
            }
            abort(404);
        }
        
        // Check if section is in use by any page sections
        $pageSectionsCount = $section->pageSections()->count();
        if ($pageSectionsCount > 0) {
            $message = "Cannot delete section because it is used by {$pageSectionsCount} page sections.";
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $message]);
            }
            return redirect()->route('admin.templates.sections.index', $template)
                    ->with('error', $message);
        }
        
        // Delete the section
        $section->delete();
        
        if ($request->ajax()) {
            return response()->json([
                'success' => true, 
                'message' => 'Section deleted successfully.'
            ]);
        }
        
        return redirect()->route('admin.templates.sections.index', $template)
                ->with('success', 'Section deleted successfully.');
    }
}
