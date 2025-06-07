<li class="dd-item template-section-item {{ getSectionColorClass($section->section_type) }}" id="section-{{ $section->id }}" data-id="{{ $section->id }}">
    <div class="dd-handle section-header">
        <div class="section-handle">
            <i class="ri-drag-move-line"></i>
        </div>
        <div class="section-title">
            <h5 class="mb-0">{{ $section->name }}</h5>
            @if($section->description)
                <small>{{ $section->description }}</small>
            @endif
        </div>
        <div class="section-position">
            Position: {{ $section->position + 1 }}
        </div>
        <div class="section-actions">
            <button type="button" class="btn btn-sm btn-primary edit-section" data-id="{{ $section->id }}" title="Edit">
                <i class="ri-pencil-line"></i>
            </button>
            <button type="button" class="btn btn-sm btn-danger delete-section" data-id="{{ $section->id }}" title="Delete">
                <i class="ri-delete-bin-line"></i>
            </button>
        </div>
    </div>
    
    <div class="section-details">
        <div class="section-info">
            <div class="info-item">
                <strong>Type:</strong> {{ $sectionTypes[$section->section_type] }}
            </div>
            
            @if($section->section_type === 'multi-column' || $section->section_type === 'full-width')
                <div class="info-item">
                    <strong>Layout:</strong> {{ $section->column_layout }}
                </div>
            @endif
            
            <div class="info-item">
                <strong>Widget Capacity:</strong>
                @if($section->is_repeatable)
                    Repeatable
                    @if($section->max_widgets)
                        (Max: {{ $section->max_widgets }})
                    @endif
                @else
                    Single Widget
                @endif
            </div>
            
            <div class="info-item">
                <strong>Slug:</strong> <code>{{ $section->slug }}</code>
            </div>
        </div>
        
        <div class="section-preview">
            @if($section->section_type === 'full-width')
                <div class="preview-full-width"></div>
            @elseif($section->section_type === 'multi-column')
                <div class="preview-multi-column">
                    @php
                        $columns = explode('-', $section->column_layout);
                    @endphp
                    
                    @foreach($columns as $column)
                        <div class="preview-column" style="flex: {{ $column }}"></div>
                    @endforeach
                </div>
            @elseif($section->section_type === 'sidebar-left')
                <div class="preview-sidebar-left">
                    <div class="preview-sidebar"></div>
                    <div class="preview-content"></div>
                </div>
            @elseif($section->section_type === 'sidebar-right')
                <div class="preview-sidebar-right">
                    <div class="preview-content"></div>
                    <div class="preview-sidebar"></div>
                </div>
            @endif
        </div>
    </div>
</li>
