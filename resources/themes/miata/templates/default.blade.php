@extends('theme::layouts.theme')

@section('content')

    <div class="col-md-12">
        @if(isset($page) && isset($sections) && count($sections) > 0)
            @foreach($page->sections()->with('templateSection')->orderBy('position')->get() as $pageSection)
                @php
                    $templateSection = $pageSection->templateSection;
                    $sectionData = $sections[$templateSection->slug] ?? null;
                @endphp
                
                @includeIf("theme::sections.{$templateSection->section_type}", [
                    'section' => $templateSection,
                    'widgets' => $sectionData['widgets'] ?? [],
                    'pageSection' => $pageSection,
                    'page' => $page,
                    'template' => $template
                ])
            @endforeach
        @elseif(isset($template) && $template->sections->count() > 0)
            <div class="alert alert-warning">
                <strong>Warning:</strong> This page has no sections configured. Showing template structure:
            </div>
            @foreach($template->sections as $section)
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="template-placeholder p-3 mb-3 bg-warning border rounded">
                            <h5>{{ $section->name }} (Template Section)</h5>
                            <small class="text-muted">Type: {{ ucfirst(str_replace('-', ' ', $section->section_type)) }}</small>
                            <div class="alert alert-info mt-2">
                                <em>This is a template section. Page sections need to be created for this page.</em>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-info">No sections defined for this template yet.</div>
                </div>
            </div>
        @endif
    </div>

@endsection