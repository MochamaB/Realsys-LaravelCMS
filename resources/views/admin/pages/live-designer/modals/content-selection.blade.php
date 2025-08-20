<!-- Content Selection Modal -->
<div class="modal fade" id="content-selection-modal" tabindex="-1" aria-labelledby="content-selection-modal-label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="content-selection-modal-label">
                    <i class="ri-file-list-3-line me-2"></i>
                    Select Content
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Content Type Selection -->
                <div class="mb-4">
                    <label class="form-label fw-semibold">Content Type</label>
                    <select class="form-select" id="content-type-select">
                        <option value="">Select content type...</option>
                        <!-- Options will be populated via JavaScript -->
                    </select>
                </div>
                
                <!-- Search and Filters -->
                <div class="row mb-3">
                    <div class="col-md-8">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="ri-search-line"></i>
                            </span>
                            <input type="text" class="form-control" placeholder="Search content..." id="content-search">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select class="form-select" id="content-sort">
                            <option value="created_at_desc">Newest First</option>
                            <option value="created_at_asc">Oldest First</option>
                            <option value="title_asc">Title A-Z</option>
                            <option value="title_desc">Title Z-A</option>
                        </select>
                    </div>
                </div>
                
                <!-- Content List -->
                <div class="content-list-container" style="max-height: 400px; overflow-y: auto;">
                    <div id="content-list">
                        <!-- Content items will be loaded here -->
                        <div class="text-center py-4 text-muted" id="content-loading">
                            <div class="spinner-border spinner-border-sm" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <div class="mt-2">Select a content type to view items</div>
                        </div>
                    </div>
                </div>
                
                <!-- Selected Content Preview -->
                <div class="mt-4" id="selected-content-preview" style="display: none;">
                    <h6 class="fw-semibold mb-3">
                        <i class="ri-eye-line me-2"></i>
                        Selected Content Preview
                    </h6>
                    <div class="card">
                        <div class="card-body">
                            <div id="content-preview-container">
                                <!-- Preview will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirm-content-selection" disabled>
                    <i class="ri-check-line me-1"></i>
                    Use Selected Content
                </button>
            </div>
        </div>
    </div>
</div>

<style>
/* Content Selection Modal Styles */
.content-item {
    border: 1px solid #e9ecef;
    border-radius: 6px;
    padding: 1rem;
    margin-bottom: 0.75rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.content-item:hover {
    border-color: #0d6efd;
    box-shadow: 0 2px 4px rgba(13, 110, 253, 0.1);
}

.content-item.selected {
    border-color: #0d6efd;
    background-color: #e3f2fd;
}

.content-item-header {
    display: flex;
    justify-content-between;
    align-items: start;
    margin-bottom: 0.5rem;
}

.content-item-title {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.25rem;
}

.content-item-type {
    font-size: 0.75rem;
    color: #6c757d;
    background: #f8f9fa;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
}

.content-item-meta {
    font-size: 0.875rem;
    color: #6c757d;
}

.content-item-excerpt {
    font-size: 0.875rem;
    color: #6c757d;
    margin-top: 0.5rem;
    line-height: 1.4;
}

.content-item-fields {
    margin-top: 0.75rem;
    padding-top: 0.75rem;
    border-top: 1px solid #e9ecef;
}

.content-field {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
}

.content-field-label {
    font-weight: 500;
    color: #495057;
}

.content-field-value {
    color: #6c757d;
    text-align: right;
    max-width: 60%;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Loading and empty states */
.content-list-empty {
    text-align: center;
    padding: 2rem;
    color: #6c757d;
}

.content-list-empty i {
    font-size: 3rem;
    margin-bottom: 1rem;
    display: block;
}

/* Preview styles */
#content-preview-container {
    max-height: 200px;
    overflow-y: auto;
}

.content-preview-field {
    margin-bottom: 0.75rem;
}

.content-preview-label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0.25rem;
}

.content-preview-value {
    color: #6c757d;
    font-size: 0.875rem;
    line-height: 1.4;
}

/* Responsive adjustments */
@media (max-width: 767.98px) {
    .modal-dialog {
        margin: 0.5rem;
    }
    
    .content-item-header {
        flex-direction: column;
        align-items: start;
    }
    
    .content-item-type {
        margin-top: 0.5rem;
    }
    
    .content-field {
        flex-direction: column;
        align-items: start;
    }
    
    .content-field-value {
        max-width: 100%;
        text-align: left;
        margin-top: 0.25rem;
    }
}
</style>

<script>
// Content Selection Modal JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('content-selection-modal');
    const contentTypeSelect = document.getElementById('content-type-select');
    const contentSearch = document.getElementById('content-search');
    const contentSort = document.getElementById('content-sort');
    const contentList = document.getElementById('content-list');
    const contentLoading = document.getElementById('content-loading');
    const selectedContentPreview = document.getElementById('selected-content-preview');
    const confirmButton = document.getElementById('confirm-content-selection');
    
    let selectedContentItem = null;
    let currentWidget = null;
    
    // Initialize modal
    modal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        currentWidget = button ? button.getAttribute('data-widget-id') : null;
        
        loadContentTypes();
        resetSelection();
    });
    
    // Content type change handler
    contentTypeSelect.addEventListener('change', function() {
        if (this.value) {
            loadContentItems(this.value);
        } else {
            showEmptyState();
        }
    });
    
    // Search handler
    let searchTimeout;
    contentSearch.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            if (contentTypeSelect.value) {
                loadContentItems(contentTypeSelect.value);
            }
        }, 300);
    });
    
    // Sort handler
    contentSort.addEventListener('change', function() {
        if (contentTypeSelect.value) {
            loadContentItems(contentTypeSelect.value);
        }
    });
    
    // Confirm selection
    confirmButton.addEventListener('click', function() {
        if (selectedContentItem && currentWidget) {
            // Emit event for parent component to handle
            const event = new CustomEvent('contentSelected', {
                detail: {
                    contentItem: selectedContentItem,
                    widgetId: currentWidget
                }
            });
            document.dispatchEvent(event);
            
            // Close modal
            bootstrap.Modal.getInstance(modal).hide();
        }
    });
    
    // Load content types
    async function loadContentTypes() {
        try {
            const response = await fetch('/admin/api/content-types');
            const contentTypes = await response.json();
            
            contentTypeSelect.innerHTML = '<option value="">Select content type...</option>';
            contentTypes.forEach(type => {
                const option = document.createElement('option');
                option.value = type.id;
                option.textContent = type.name;
                contentTypeSelect.appendChild(option);
            });
        } catch (error) {
            console.error('Failed to load content types:', error);
        }
    }
    
    // Load content items
    async function loadContentItems(contentTypeId) {
        showLoading();
        
        try {
            const params = new URLSearchParams({
                search: contentSearch.value,
                sort: contentSort.value
            });
            
            const response = await fetch(`/admin/api/content-types/${contentTypeId}/items?${params}`);
            const contentItems = await response.json();
            
            renderContentItems(contentItems);
        } catch (error) {
            console.error('Failed to load content items:', error);
            showError('Failed to load content items');
        }
    }
    
    // Render content items
    function renderContentItems(items) {
        if (items.length === 0) {
            showEmptyState();
            return;
        }
        
        contentList.innerHTML = items.map(item => `
            <div class="content-item" data-content-id="${item.id}" onclick="selectContentItem(${item.id}, this)">
                <div class="content-item-header">
                    <div>
                        <div class="content-item-title">${item.title || 'Untitled'}</div>
                        <div class="content-item-meta">
                            Created: ${new Date(item.created_at).toLocaleDateString()}
                            ${item.updated_at !== item.created_at ? '• Updated: ' + new Date(item.updated_at).toLocaleDateString() : ''}
                        </div>
                    </div>
                    <div class="content-item-type">${item.content_type.name}</div>
                </div>
                ${item.excerpt ? `<div class="content-item-excerpt">${item.excerpt}</div>` : ''}
                <div class="content-item-fields">
                    ${item.field_values.slice(0, 3).map(field => `
                        <div class="content-field">
                            <span class="content-field-label">${field.field.name}:</span>
                            <span class="content-field-value">${truncateText(field.value, 30)}</span>
                        </div>
                    `).join('')}
                    ${item.field_values.length > 3 ? `<div class="text-muted small">+${item.field_values.length - 3} more fields</div>` : ''}
                </div>
            </div>
        `).join('');
    }
    
    // Select content item
    window.selectContentItem = function(contentId, element) {
        // Remove previous selection
        document.querySelectorAll('.content-item').forEach(item => {
            item.classList.remove('selected');
        });
        
        // Select current item
        element.classList.add('selected');
        selectedContentItem = contentId;
        
        // Enable confirm button
        confirmButton.disabled = false;
        
        // Load preview
        loadContentPreview(contentId);
    };
    
    // Load content preview
    async function loadContentPreview(contentId) {
        try {
            const response = await fetch(`/admin/api/content-items/${contentId}/preview`);
            const preview = await response.json();
            
            document.getElementById('content-preview-container').innerHTML = preview.html;
            selectedContentPreview.style.display = 'block';
        } catch (error) {
            console.error('Failed to load content preview:', error);
        }
    }
    
    // Utility functions
    function showLoading() {
        contentList.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border spinner-border-sm" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div class="mt-2 text-muted">Loading content items...</div>
            </div>
        `;
    }
    
    function showEmptyState() {
        contentList.innerHTML = `
            <div class="content-list-empty">
                <i class="ri-file-list-3-line"></i>
                <div>No content items found</div>
                <div class="small text-muted mt-1">Try selecting a different content type or adjusting your search</div>
            </div>
        `;
    }
    
    function showError(message) {
        contentList.innerHTML = `
            <div class="content-list-empty">
                <i class="ri-error-warning-line text-danger"></i>
                <div class="text-danger">${message}</div>
            </div>
        `;
    }
    
    function resetSelection() {
        selectedContentItem = null;
        confirmButton.disabled = true;
        selectedContentPreview.style.display = 'none';
        contentTypeSelect.value = '';
        contentSearch.value = '';
        contentSort.value = 'created_at_desc';
        showEmptyState();
    }
    
    function truncateText(text, maxLength) {
        if (text.length <= maxLength) return text;
        return text.substring(0, maxLength) + '...';
    }
});
</script>
