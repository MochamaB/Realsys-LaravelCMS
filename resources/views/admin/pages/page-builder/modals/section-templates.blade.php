<!-- Section Templates Modal -->
<div class="modal fade" id="sectionTemplatesModal" tabindex="-1" aria-labelledby="sectionTemplatesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white pb-2" id="sectionTemplatesModalLabel">
                    <i class="ri-layout-grid-line me-2"></i>Choose Section Template
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 mb-3">
                    <div class="col-12">
                        <div class="alert alert-info border-0">
                            <i class="ri-information-line me-2"></i>
                            <strong>Select a Section Template:</strong> Choose from available section layouts to add to your page.
                            Templates are loaded from your active theme and core system templates.
                        </div>
                    </div>
                </div>
                
                <!-- Templates Grid -->
                <div class="row g-3" id="sectionTemplatesGrid">
                    @if(isset($sectionTemplates) && isset($sectionTemplates['templates']) && count($sectionTemplates['templates']) > 0)
                        @foreach($sectionTemplates['templates'] as $template)
                        <div class="col-lg-4 col-md-6">
                            <div class="card section-template-card h-100" 
                                 data-template-key="{{ $template['key'] }}" 
                                 data-template-type="{{ $template['type'] }}"
                                 data-template-name="{{ $template['name'] }}"
                                 draggable="true"
                                 data-gs-width="12"
                                 data-gs-height="4"
                                 style="cursor: grab; transition: all 0.2s ease;">
                                <div class="card-body text-center p-4">
                                    <!-- Template Icon -->
                                    <div class="mb-3">
                                        <i class="{{ $template['icon'] ?? 'ri-layout-grid-line' }} display-4 text-primary"></i>
                                    </div>
                                    
                                    <!-- Template Name -->
                                    <h6 class="card-title mb-2">{{ $template['name'] }}</h6>
                                    
                                    <!-- Template Description -->
                                    <p class="text-muted small mb-3">{{ $template['description'] ?? 'No description available' }}</p>
                                    
                                    <!-- Template Meta -->
                                    <div class="template-meta">
                                        <span class="badge bg-{{ $template['type'] == 'core' ? 'primary' : 'warning' }} text-white small">
                                            {{ ucfirst($template['type']) }}
                                        </span>
                                        @if(isset($template['category']))
                                        <span class="badge bg-light text-dark small ms-1">
                                            {{ ucfirst($template['category']) }}
                                        </span>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- Preview Image (if available) -->
                                @if(isset($template['preview_image']) && $template['preview_image'])
                                <div class="card-footer p-0">
                                    <img src="{{ $template['preview_image'] }}" 
                                         alt="{{ $template['name'] }} preview" 
                                         class="img-fluid"
                                         style="height: 100px; width: 100%; object-fit: cover;"
                                         onerror="this.style.display='none'">
                                </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    @else
                        <!-- Empty State -->
                        <div class="col-12">
                            <div class="text-center py-5">
                                <div class="mb-4">
                                    <i class="ri-layout-grid-line display-1 text-muted"></i>
                                </div>
                                <h5 class="text-muted mb-2">No Section Templates Found</h5>
                                <p class="text-muted mb-4">
                                    No section templates are available. Please ensure your theme is properly configured
                                    or check that core templates are available.
                                </p>
                                <div class="alert alert-warning">
                                    <small>
                                        <strong>Debug Info:</strong><br>
                                        Active Theme: {{ $sectionTemplates['theme']['name'] ?? 'None' }}<br>
                                        Templates Count: {{ $sectionTemplates['total_count'] ?? 0 }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="modal-footer d-flex justify-content-between align-items-center">
                <div class="template-info">
                    <small class="text-muted">
                        <i class="ri-information-line me-1"></i>
                        <span id="selectedTemplateInfo">Select a template to continue</span>
                    </small>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="ri-close-line me-2"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-success" id="addSelectedSectionBtn" disabled>
                        <i class="ri-add-line me-2"></i>Add Section
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Section Templates Modal Handler
    const modal = document.getElementById('sectionTemplatesModal');
    const templateCards = document.querySelectorAll('.section-template-card');
    const addBtn = document.getElementById('addSelectedSectionBtn');
    const templateInfo = document.getElementById('selectedTemplateInfo');
    let selectedTemplate = null;
    
    if (!modal || !addBtn) return;
    
    // Handle template card selection
    templateCards.forEach(card => {
        card.addEventListener('click', function() {
            // Remove previous selection
            templateCards.forEach(c => {
                c.classList.remove('border-primary', 'bg-primary', 'bg-opacity-10');
                c.style.borderWidth = '';
                c.style.transform = '';
                c.style.boxShadow = '';
            });
            
            // Add selection to clicked card
            this.classList.add('border-primary', 'bg-primary', 'bg-opacity-10');
            this.style.borderWidth = '2px';
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 4px 12px rgba(13, 110, 253, 0.25)';
            
            // Store selected template data
            selectedTemplate = {
                key: this.dataset.templateKey,
                type: this.dataset.templateType,
                name: this.dataset.templateName || this.querySelector('.card-title').textContent
            };
            
            // Update UI
            addBtn.disabled = false;
            templateInfo.innerHTML = `<strong>${selectedTemplate.name}</strong> (${selectedTemplate.type}) selected`;
            
            console.log('📋 Template selected:', selectedTemplate);
        });
        
        // Add hover effects
        card.addEventListener('mouseenter', function() {
            if (!this.classList.contains('border-primary')) {
                this.style.borderColor = '#0d6efd';
                this.style.transform = 'translateY(-2px)';
                this.style.boxShadow = '0 2px 8px rgba(0,0,0,0.1)';
            }
        });
        
        card.addEventListener('mouseleave', function() {
            if (!this.classList.contains('border-primary')) {
                this.style.borderColor = '';
                this.style.transform = '';
                this.style.boxShadow = '';
            }
        });
    });
    
    // Handle add section button click
    addBtn.addEventListener('click', function() {
        if (selectedTemplate) {
            console.log('✅ Creating section:', selectedTemplate);
            
            // TODO: Replace with actual API call in next phase
            alert(`Section "${selectedTemplate.name}" will be created!\n\nTemplate: ${selectedTemplate.key}\nType: ${selectedTemplate.type}\n\nThis will be connected to the backend API in the next phase.`);
            
            // Close modal
            const bsModal = bootstrap.Modal.getInstance(modal);
            bsModal.hide();
        }
    });
    
    // Reset selection when modal is hidden
    modal.addEventListener('hidden.bs.modal', function() {
        templateCards.forEach(c => {
            c.classList.remove('border-primary', 'bg-primary', 'bg-opacity-10');
            c.style.borderWidth = '';
            c.style.borderColor = '';
            c.style.transform = '';
            c.style.boxShadow = '';
        });
        selectedTemplate = null;
        addBtn.disabled = true;
        templateInfo.textContent = 'Select a template to continue';
    });
    
    console.log('🎨 Section templates modal initialized with', templateCards.length, 'templates');
});
</script>
@endpush