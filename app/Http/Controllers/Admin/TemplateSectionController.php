<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Template;
use App\Models\TemplateSection;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class TemplateSectionController extends Controller
{
    /**
     * Display a listing of the sections for a template.
     * Used by JS: loadExistingSections()
     */
    public function index(Template $template): JsonResponse
    {
        $sections = $template->sections()
            ->orderBy('position')
            ->orderBy('y')
            ->get();

        return response()->json([
            'success' => true,
            'sections' => $sections
        ]);
    }

    /**
     * Show the form for creating a new section.
     */
    public function create(Template $template)
    {
        // Return view for creating section (if needed)
        return view('admin.templates.sections.create', compact('template'));
    }

    /**
     * Store a newly created section.
     * Used by JS: autoSaveNewSection()
     */
    public function store(Request $request, Template $template): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'section_type' => 'required|string',
            'column_layout' => 'required|string',
            'position' => 'required|integer',
            'description' => 'nullable|string',
            'is_repeatable' => 'boolean',
            'max_widgets' => 'nullable|integer',
            'css_classes' => 'nullable|string',
            'x' => 'required|integer',
            'y' => 'required|integer', 
            'w' => 'required|integer',
            'h' => 'required|integer'
        ]);

        $slug = Str::slug($validated['name'] ?? 'section');
        $count = 0;
        $originalSlug = $slug;
        while (TemplateSection::where('template_id', $template->id)
                            ->where('slug', $slug)
                            ->exists()) {
            $count++;
            $slug = $originalSlug . '-' . $count;
        }

        $section = $template->sections()->create([
            'name' => $request->name,
            'slug' => $slug,
            'section_type' => $request->section_type,
            'column_layout' => $request->column_layout,
            'position' => $request->position,
            'description' => $request->description,
            'is_repeatable' => $request->boolean('is_repeatable'),
            'max_widgets' => $request->max_widgets ?? 0,
            'css_classes' => $request->css_classes,
            'x' => $request->x,
            'y' => $request->y,
            'w' => $request->w,
            'h' => $request->h
        ]);

        return response()->json([
            'success' => true,
            'section' => $section,
            'message' => 'Section created successfully'
        ]);
    }

    /**
     * Display the specified section.
     */
    public function show(Template $template, TemplateSection $section)
    {
        return view('admin.templates.sections.show', compact('template', 'section'));
    }

    /**
     * Show the form for editing the specified section.
     */
    public function edit(Template $template, TemplateSection $section)
    {
        return view('admin.templates.sections.edit', compact('template', 'section'));
    }

    /**
     * Update the specified section.
     * Used by JS: updateSectionOnServer()
     */
    public function update(Request $request, Template $template, TemplateSection $section): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'section_type' => 'required|string',
            'column_layout' => 'required|string',
            'position' => 'required|integer',
            'description' => 'nullable|string',
            'max_widgets' => 'nullable|integer',
            'x' => 'required|integer',
            'y' => 'required|integer',
            'w' => 'required|integer',
            'h' => 'required|integer'
        ]);
    
        // If the name has changed, regenerate the slug and ensure uniqueness
        if ($section->name !== $validated['name']) {
            $slug = \Str::slug($validated['name']);
            $count = 0;
            $originalSlug = $slug;
    
            while (
                TemplateSection::where('template_id', $template->id)
                    ->where('slug', $slug)
                    ->where('id', '!=', $section->id)
                    ->exists()
            ) {
                $count++;
                $slug = $originalSlug . '-' . $count;
            }
    
            $section->slug = $slug;
        }
    
        $section->update([
            'name' => $validated['name'],
            'section_type' => $validated['section_type'],
            'column_layout' => $validated['column_layout'],
            'position' => $validated['position'],
            'description' => $validated['description'],
            'max_widgets' => $validated['max_widgets'] ?? 0,
            'x' => $validated['x'],
            'y' => $validated['y'],
            'w' => $validated['w'],
            'h' => $validated['h']
        ]);
    
        return response()->json([
            'success' => true,
            'section' => $section->fresh(),
            'message' => 'Section updated successfully'
        ]);
    }
    

    /**
     * Remove the specified section.
     * Used by JS: deleteSectionFromServer()
     */
    public function destroy(Template $template, TemplateSection $section): JsonResponse
    {
        try {
            $section->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Section deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting section: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update positions of sections.
     * Used by JS: Save Layout button
     */
    public function updatePositions(Request $request, Template $template): JsonResponse
    {
        $request->validate([
            'positions' => 'required|array',
            'positions.*.id' => 'required|exists:template_sections,id',
            'positions.*.position' => 'required|integer',
            'positions.*.x' => 'required|integer',
            'positions.*.y' => 'required|integer',
            'positions.*.w' => 'required|integer',
            'positions.*.h' => 'required|integer'
        ]);

        try {
            foreach ($request->positions as $positionData) {
                $section = $template->sections()->find($positionData['id']);
                if ($section) {
                    $section->update([
                        'position' => $positionData['position'],
                        'x' => $positionData['x'],
                        'y' => $positionData['y'],
                        'w' => $positionData['w'],
                        'h' => $positionData['h']
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Positions updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating positions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific section (additional helper method).
     */
    public function getSection(Template $template, TemplateSection $section): JsonResponse
    {
        return response()->json([
            'success' => true,
            'section' => $section
        ]);
    }
}