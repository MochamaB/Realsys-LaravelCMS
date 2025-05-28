@extends('admin.layouts.master')

@section('title', 'Template Preview - ' . $template->name)
@section('page-title', 'Template Preview')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Preview: {{ $template->name }}</h5>
                    <div>
                        <a href="{{ route('admin.templates.edit', $template) }}" class="btn btn-primary me-2">
                            <i class="mdi mdi-pencil me-1"></i> Edit Template
                        </a>
                        <a href="{{ route('admin.templates.show', $template) }}" class="btn btn-info me-2">
                            <i class="mdi mdi-eye me-1"></i> Details
                        </a>
                        <a href="{{ route('admin.templates.index') }}" class="btn btn-secondary">
                            <i class="mdi mdi-arrow-left me-1"></i> Back to Templates
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    @if($template->thumbnail_path)
                                        <img src="{{ asset($template->thumbnail_path) }}" alt="{{ $template->name }}" class="img-thumbnail" style="max-width: 120px;">
                                    @else
                                        <div class="bg-light d-flex align-items-center justify-content-center rounded" style="width: 120px; height: 90px;">
                                            <i class="mdi mdi-file-outline text-muted" style="font-size: 48px;"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h4>{{ $template->name }}</h4>
                                    <p class="text-muted mb-1">{{ $template->description ?? 'No description' }}</p>
                                    <div>
                                        <span class="badge bg-primary">{{ $template->theme->name }}</span>
                                        @if($template->is_default)
                                            <span class="badge bg-success ms-1">Default Template</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <div class="text-muted">
                                <strong>Template File:</strong> <code>{{ $template->file_path }}</code>
                            </div>
                            <div class="text-muted">
                                <strong>Sections:</strong> {{ $template->sections->count() }}
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <h5 class="border-bottom pb-2 mb-3">Layout Preview</h5>
                            
                            <div class="template-preview bg-light p-3">
                                <div class="row">
                                    @php
                                        // Get sorted sections
                                        $sections = $template->sections->sortBy('order_index');
                                        
                                        // Group sections by type to help with layout
                                        $headerSections = $sections->filter(function($section) {
                                            return $section->isType('header');
                                        });
                                        
                                        $footerSections = $sections->filter(function($section) {
                                            return $section->isType('footer');
                                        });
                                        
                                        $contentSections = $sections->filter(function($section) {
                                            return !$section->isType('header') && !$section->isType('footer');
                                        });
                                    @endphp
                                    
                                    <!-- Header Sections -->
                                    @foreach($headerSections as $section)
                                        <div class="{{ $section->width ?? 'col-12' }} mb-3">
                                            <div class="preview-section bg-primary bg-opacity-25 p-3 rounded border border-primary">
                                                <h6 class="preview-section-title mb-2">{{ $section->name }}</h6>
                                                <div class="preview-section-type small mb-2">
                                                    <span class="badge bg-primary">{{ $section->type }}</span>
                                                    @if($section->is_required)
                                                        <span class="badge bg-danger ms-1">Required</span>
                                                    @endif
                                                </div>
                                                <div class="preview-section-widgets">
                                                    <i class="mdi mdi-widgets text-primary me-1"></i>
                                                    <span class="text-muted small">
                                                        {{ $section->max_widgets ? "Max {$section->max_widgets} widgets" : "Unlimited widgets" }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                    
                                    <!-- Content Sections -->
                                    @foreach($contentSections as $section)
                                        <div class="{{ $section->width ?? 'col-md-6' }} mb-3">
                                            @php
                                                $bgClass = match($section->type) {
                                                    'hero' => 'bg-info bg-opacity-25 border-info',
                                                    'sidebar' => 'bg-warning bg-opacity-25 border-warning',
                                                    'banner' => 'bg-success bg-opacity-25 border-success',
                                                    'content' => 'bg-secondary bg-opacity-25 border-secondary',
                                                    default => 'bg-dark bg-opacity-10 border-dark',
                                                };
                                            @endphp
                                            <div class="preview-section {{ $bgClass }} p-3 rounded border">
                                                <h6 class="preview-section-title mb-2">{{ $section->name }}</h6>
                                                <div class="preview-section-type small mb-2">
                                                    <span class="badge bg-secondary">{{ $section->type }}</span>
                                                    @if($section->is_required)
                                                        <span class="badge bg-danger ms-1">Required</span>
                                                    @endif
                                                </div>
                                                <div class="preview-section-widgets">
                                                    <i class="mdi mdi-widgets text-muted me-1"></i>
                                                    <span class="text-muted small">
                                                        {{ $section->max_widgets ? "Max {$section->max_widgets} widgets" : "Unlimited widgets" }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                    
                                    <!-- Footer Sections -->
                                    @foreach($footerSections as $section)
                                        <div class="{{ $section->width ?? 'col-12' }} mb-3">
                                            <div class="preview-section bg-dark bg-opacity-25 p-3 rounded border border-dark">
                                                <h6 class="preview-section-title mb-2">{{ $section->name }}</h6>
                                                <div class="preview-section-type small mb-2">
                                                    <span class="badge bg-dark">{{ $section->type }}</span>
                                                    @if($section->is_required)
                                                        <span class="badge bg-danger ms-1">Required</span>
                                                    @endif
                                                </div>
                                                <div class="preview-section-widgets">
                                                    <i class="mdi mdi-widgets text-muted me-1"></i>
                                                    <span class="text-muted small">
                                                        {{ $section->max_widgets ? "Max {$section->max_widgets} widgets" : "Unlimited widgets" }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            
                            @if($sections->isEmpty())
                                <div class="alert alert-info mt-3">
                                    <i class="mdi mdi-information-outline me-2"></i>
                                    This template has no sections defined yet. Add sections to visualize the template structure.
                                </div>
                                <a href="{{ route('admin.templates.sections.create', $template) }}" class="btn btn-primary mt-2">
                                    <i class="mdi mdi-plus-circle me-1"></i> Add Section
                                </a>
                            @endif
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="alert alert-warning">
                                <i class="mdi mdi-alert-outline me-2"></i>
                                <strong>Note:</strong> This is a visual representation of the template structure and does not reflect the actual design of the template when rendered on the frontend.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
