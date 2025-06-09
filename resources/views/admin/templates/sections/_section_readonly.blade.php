<li class="dd-item template-section-item" id="section-{{ $section->id }}">
    <div class="section-header">
        <div class="section-title">
            <h5>{{ $section->name }}</h5>
            @if($section->description)
                <small>{{ Str::limit($section->description, 80) }}</small>
            @endif
        </div>
        <div class="section-position">
            Position: {{ $section->position + 1 }}
        </div>
    </div>
    <div class="section-details">
        <div class="section-info">
            <div class="info-item">
                <strong>Type:</strong>
                <span class="badge badge-info">{{ $sectionTypes[$section->section_type] ?? 'Unknown' }}</span>
            </div>
            
            @if($section->section_type === 'multi-column' || $section->section_type === 'full-width')
                <div class="info-item">
                    <strong>Layout:</strong>
                    <span class="text-muted">{{ $section->column_layout }}</span>
                </div>
            @endif
            
            <div class="info-item">
                <strong>Widget Capacity:</strong>
                @if($section->is_repeatable)
                    <span class="badge badge-success">Repeatable</span>
                    @if($section->max_widgets)
                        <small class="text-muted">(Max: {{ $section->max_widgets }})</small>
                    @endif
                @else
                    <span class="badge badge-secondary">Single Widget</span>
                @endif
            </div>
            
            <div class="info-item">
                <strong>Slug:</strong> 
                <code>{{ $section->slug }}</code>
            </div>
            
            @if($section->created_at)
                <div class="info-item">
                    <strong>Created:</strong>
                    <small class="text-muted">{{ $section->created_at->format('M j, Y') }}</small>
                </div>
            @endif
        </div>
        
        <div class="section-preview" title="Section Layout Preview">
            @if($section->section_type === 'full-width')
                <div class="preview-full-width">
                    <div class="preview-label">Full Width</div>
                </div>
            @elseif($section->section_type === 'multi-column')
                <div class="preview-multi-column">
                    @php
                        $columns = explode('-', $section->column_layout ?? '12');
                    @endphp
                    
                    @foreach($columns as $column)
                        <div class="preview-column" 
                             style="flex: {{ $column }}"
                             title="Column width: {{ $column }}"></div>
                    @endforeach
                </div>
            @elseif($section->section_type === 'sidebar-left')
                <div class="preview-sidebar-left">
                    <div class="preview-sidebar" title="Sidebar"></div>
                    <div class="preview-content" title="Main Content"></div>
                </div>
            @elseif($section->section_type === 'sidebar-right')
                <div class="preview-sidebar-right">
                    <div class="preview-content" title="Main Content"></div>
                    <div class="preview-sidebar" title="Sidebar"></div>
                </div>
            @else
                <div class="preview-default">
                    <div class="preview-placeholder">
                        <i class="ri-layout-line"></i>
                    </div>
                </div>
            @endif
        </div>
    </div>
</li>
