{{-- Content Type Properties Partial View --}}
<div class="row">
    <!-- Content Type Details -->
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bx bx-info-circle"></i> Content Type Details
                </h5>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-4">Name</dt>
                    <dd class="col-sm-8">{{ $contentType->name }}</dd>
                    
                    <dt class="col-sm-4">Identifier</dt>
                    <dd class="col-sm-8">
                        <code class="bg-light px-2 py-1 rounded">{{ $contentType->slug }}</code>
                    </dd>
                    
                    <dt class="col-sm-4">Status</dt>
                    <dd class="col-sm-8">
                        @if($contentType->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Inactive</span>
                        @endif
                    </dd>

                    <dt class="col-sm-4">System Type</dt>
                    <dd class="col-sm-8">
                        @if($contentType->is_system)
                            <span class="badge bg-warning">System</span>
                            <small class="text-muted d-block">System types cannot be deleted</small>
                        @else
                            <span class="badge bg-info">Custom</span>
                        @endif
                    </dd>

                    <dt class="col-sm-4">Fields</dt>
                    <dd class="col-sm-8">{{ $contentType->fields->count() }}</dd>

                    <dt class="col-sm-4">Content Items</dt>
                    <dd class="col-sm-8">{{ $contentType->contentItems->count() }}</dd>
                    
                    <dt class="col-sm-4">Created</dt>
                    <dd class="col-sm-8">
                        {{ $contentType->created_at->format('M d, Y') }}
                        @if($contentType->creator)
                            <small class="text-muted d-block">by {{ $contentType->creator->name }}</small>
                        @endif
                    </dd>
                    
                    <dt class="col-sm-4">Updated</dt>
                    <dd class="col-sm-8">
                        {{ $contentType->updated_at->format('M d, Y') }}
                        @if($contentType->updater)
                            <small class="text-muted d-block">by {{ $contentType->updater->name }}</small>
                        @endif
                    </dd>
                </dl>
                
                @if($contentType->description)
                    <hr>
                    <div>
                        <strong>Description:</strong>
                        <p class="mb-0 mt-2">{{ $contentType->description }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Content Type Statistics -->
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="bx bx-bar-chart-alt"></i> Statistics
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-6">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <h3 class="text-primary mb-0">{{ $contentType->fields->count() }}</h3>
                                <p class="text-muted mb-0">Fields</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <h3 class="text-success mb-0">{{ $contentType->contentItems->count() }}</h3>
                                <p class="text-muted mb-0">Content Items</p>
                            </div>
                        </div>
                    </div>
                    
                    @if($contentType->contentItems->count() > 0)
                        <div class="col-6">
                            <div class="card border-0 bg-light">
                                <div class="card-body text-center">
                                    <h3 class="text-success mb-0">
                                        {{ $contentType->contentItems->filter(function($item) { return $item->status === 'published' || $item->status === 'active'; })->count() }}
                                    </h3>
                                    <p class="text-muted mb-0">Published</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="card border-0 bg-light">
                                <div class="card-body text-center">
                                    <h3 class="text-secondary mb-0">
                                        {{ $contentType->contentItems->filter(function($item) { return $item->status === 'draft' || $item->status === 'inactive'; })->count() }}
                                    </h3>
                                    <p class="text-muted mb-0">Drafts</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                
                @if($contentType->contentItems->count() > 0)
                    <hr>
                    <div class="d-flex justify-content-center">
                        <div class="text-center">
                            <p class="mb-1">Recent Activity</p>
                            @php
                                $lastCreated = $contentType->contentItems->sortByDesc('created_at')->first();
                                $lastUpdated = $contentType->contentItems->sortByDesc('updated_at')->first();
                            @endphp
                            @if($lastCreated)
                                <p class="text-muted small mb-0">
                                    Last item created: {{ $lastCreated->created_at->diffForHumans() }}
                                </p>
                            @endif
                            @if($lastUpdated)
                                <p class="text-muted small mb-0">
                                    Last item updated: {{ $lastUpdated->updated_at->diffForHumans() }}
                                </p>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Associated Widgets -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bx bx-layout"></i> Associated Widgets
                </h5>
            </div>
            <div class="card-body">
                @if(method_exists($contentType, 'widgets') && $contentType->relationLoaded('widgets'))
                    @if($contentType->widgets->isEmpty())
                        <div class="text-center py-3">
                            <p class="text-muted mb-0">No widgets associated with this content type</p>
                        </div>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach($contentType->widgets as $widget)
                                <div class="list-group-item px-0">
                                    <div class="d-flex align-items-center">
                                        <div>
                                            <i class="bx bx-{{ $widget->icon ?? 'widget' }} me-2"></i>
                                            <strong>{{ $widget->name }}</strong>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @else
                    <div class="text-center py-3">
                        <p class="text-muted mb-0">Widget associations not available</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
