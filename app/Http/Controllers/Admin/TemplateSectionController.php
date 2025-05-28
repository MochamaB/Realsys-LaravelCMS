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
            $query->orderBy('order_index');
        }]);
        
        $sectionTypes = TemplateSection::getTypes();
        
        return view('admin.template_sections.index', compact('template', 'sectionTypes'));
    }

    /**
     * Show the form for creating a new section.
     */
    public function create(Template $template)
    {
        $sectionTypes = TemplateSection::getTypes();
        $nextOrderIndex = $template->sections()->max('order_index') + 1;
        
        return view('admin.template_sections.create', compact('template', 'sectionTypes', 'nextOrderIndex'));
    }

    /**
     * Store a newly created section in storage.
     */
    public function store(Request $request, Template $template)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => [
                'required',
                Rule::in(array_keys(TemplateSection::getTypes()))
            ],
            'width' => 'nullable|string|max:50',
            'is_required' => 'boolean',
            'max_widgets' => 'nullable|integer|min:1',
            'order_index' => 'nullable|integer|min:0',
            'settings' => 'nullable|array',
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
        
        // Set order index if not provided
        if (!isset($validated['order_index'])) {
            $validated['order_index'] = $template->sections()->max('order_index') + 1;
        }
        
        // Create the section
        $section = TemplateSection::create([
            'template_id' => $template->id,
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'],
            'width' => $validated['width'] ?? null,
            'is_required' => $request->has('is_required') && $request->is_required ? true : false,
            'max_widgets' => $validated['max_widgets'] ?? null,
            'order_index' => $validated['order_index'],
            'settings' => $validated['settings'] ?? null,
            'is_active' => true,
        ]);
        
        // If width is null, set default width based on section type
        if (empty($section->width)) {
            $section->width = $section->getDefaultWidth();
            $section->save();
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
     * Show the form for editing the specified section.
     */
    public function edit(Template $template, TemplateSection $section)
    {
        // Ensure the section belongs to the template
        if ($section->template_id !== $template->id) {
            abort(404);
        }
        
        $sectionTypes = TemplateSection::getTypes();
        
        return view('admin.template_sections.edit', compact('template', 'section', 'sectionTypes'));
    }

    /**
     * Update the specified section in storage.
     */
    public function update(Request $request, Template $template, TemplateSection $section)
    {
        // Ensure the section belongs to the template
        if ($section->template_id !== $template->id) {
            abort(404);
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => [
                'required',
                Rule::in(array_keys(TemplateSection::getTypes()))
            ],
            'width' => 'nullable|string|max:50',
            'is_required' => 'boolean',
            'max_widgets' => 'nullable|integer|min:1',
            'order_index' => 'nullable|integer|min:0',
            'settings' => 'nullable|array',
        ]);
        
        // Update the section
        $section->name = $validated['name'];
        $section->description = $validated['description'] ?? null;
        $section->type = $validated['type'];
        $section->width = $validated['width'] ?? $section->getDefaultWidth();
        $section->is_required = $request->has('is_required') && $request->is_required ? true : false;
        $section->max_widgets = $validated['max_widgets'] ?? null;
        $section->order_index = $validated['order_index'] ?? $section->order_index;
        $section->settings = $validated['settings'] ?? $section->settings;
        $section->save();
        
        return redirect()->route('admin.templates.sections.index', $template)
                ->with('success', 'Section updated successfully.');
    }

    /**
     * Update the order of sections.
     */
    public function updateOrder(Request $request, Template $template)
    {
        $validated = $request->validate([
            'sections' => 'required|array',
            'sections.*.id' => 'required|exists:template_sections,id',
            'sections.*.order_index' => 'required|integer|min:0',
        ]);
        
        foreach ($validated['sections'] as $sectionData) {
            TemplateSection::where('id', $sectionData['id'])
                ->where('template_id', $template->id)
                ->update(['order_index' => $sectionData['order_index']]);
        }
        
        return response()->json(['success' => true]);
    }

    /**
     * Remove the specified section from storage.
     */
    public function destroy(Template $template, TemplateSection $section)
    {
        // Ensure the section belongs to the template
        if ($section->template_id !== $template->id) {
            abort(404);
        }
        
        // Check if section is in use by any page sections
        $pageSectionsCount = $section->pageSections()->count();
        if ($pageSectionsCount > 0) {
            return redirect()->route('admin.templates.sections.index', $template)
                    ->with('error', "Cannot delete section because it is used by {$pageSectionsCount} page sections.");
        }
        
        // Delete the section
        $section->delete();
        
        return redirect()->route('admin.templates.sections.index', $template)
                ->with('success', 'Section deleted successfully.');
    }
}
