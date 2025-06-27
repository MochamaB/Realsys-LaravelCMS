<div class="card">
                <div class="card-body">
                    <div class="row justify-content-center">
                        <div class="col-md-8 text-center">
                            <h5 class="card-title mb-3">Widget Preview</h5>
                            @php
                                $previewPath = "/themes/{$widget->theme->slug}/widgets/{$widget->slug}/preview.png";
                                $previewExists = file_exists(public_path($previewPath));
                            @endphp
                            
                            @if($previewExists)
                                <img src="{{ asset($previewPath) }}" class="img-fluid rounded shadow-sm" alt="{{ $widget->name }} Preview" style="max-height: 500px;">
                            @else
                                <div class="alert alert-info">
                                    <i class="bx bx-info-circle me-2"></i> No preview image available for this widget.
                                </div>
                                <div class="border rounded p-5 bg-light text-center">
                                    <i class="bx bx-image" style="font-size: 5rem; opacity: 0.2;"></i>
                                    <h5 class="mt-3 text-muted">Preview Not Available</h5>
                                </div>
                            @endif
                            
                            <div class="mt-4">
                                <p class="text-muted">This is a visual preview of how this widget appears when rendered. The actual appearance may vary based on content and settings.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>