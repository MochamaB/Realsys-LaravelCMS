/**
 * Content Browser Modal
 * Allows users to browse and select content items for widgets
 */
class ContentBrowser {
    constructor(api) {
        this.api = api;
        this.pageId = null;
        this.contentTypes = [];
        this.currentContentType = null;
        this.currentPage = 1;
        this.itemsPerPage = 12;
        this.searchQuery = '';
        this.selectedItems = [];
        this.onContentSelect = null; // Callback for content selection
        this.currentComponent = null;
        
        this.modal = null;
        this.isOpen = false;
        
        console.log('🗂️ ContentBrowser initialized');
        
        // Create modal on initialization
        this.createModal();
    }
    
    /**
     * Initialize content browser
     */
    async initialize(pageId) {
        this.pageId = pageId;
        
        try {
            const response = await this.api.loadContentTypes(pageId);
            if (response.success) {
                this.contentTypes = response.data.content_types || [];
            }
        } catch (error) {
            console.error('Error loading content types:', error);
        }
    }
    
    /**
     * Open content browser modal
     */
    async open(contentTypeId = null, component = null) {
        this.currentComponent = component;
        this.selectedItems = [];
        
        if (contentTypeId) {
            this.currentContentType = this.contentTypes.find(ct => ct.id == contentTypeId);
        } else if (this.contentTypes.length > 0) {
            this.currentContentType = this.contentTypes[0];
        }
        
        this.isOpen = true;
        this.modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        await this.renderContent();
        
        console.log('📂 Content browser opened');
    }
    
    /**
     * Close content browser modal
     */
    close() {
        this.isOpen = false;
        this.modal.classList.add('hidden');
        document.body.style.overflow = '';
        this.currentComponent = null;
        
        console.log('📂 Content browser closed');
    }
    
    /**
     * Create modal DOM structure
     */
    createModal() {
        const modalHtml = `
            <div class="content-browser-modal hidden">
                <div class="content-browser-dialog">
                    <div class="content-browser-header">
                        <h2 class="content-browser-title">Browse Content</h2>
                        <button class="content-browser-close" data-close-browser>×</button>
                    </div>
                    <div class="content-browser-filters">
                        <div class="filter-row">
                            <div class="filter-group">
                                <label class="form-label">Content Type</label>
                                <select class="form-control form-select" data-content-type-filter>
                                    <option value="">Loading...</option>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label class="form-label">Search</label>
                                <input type="text" class="form-control" placeholder="Search content..." data-search-input>
                            </div>
                            <div class="filter-group">
                                <label class="form-label">&nbsp;</label>
                                <button class="btn-primary" data-search-button>Search</button>
                            </div>
                        </div>
                    </div>
                    <div class="content-browser-body">
                        <div class="content-items-container">
                            <!-- Content items will be loaded here -->
                        </div>
                    </div>
                    <div class="content-browser-footer">
                        <div class="pagination-info">
                            <span data-pagination-info>-</span>
                        </div>
                        <div class="pagination-controls">
                            <button class="pagination-btn" data-prev-page disabled>Previous</button>
                            <button class="pagination-btn" data-next-page disabled>Next</button>
                        </div>
                        <div class="content-browser-actions">
                            <button class="btn-secondary" data-close-browser>Cancel</button>
                            <button class="btn-primary" data-select-content disabled>
                                Select Content (<span data-selected-count>0</span>)
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Add modal to body
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        this.modal = document.querySelector('.content-browser-modal');
        
        this.attachModalEventListeners();
    }
    
    /**
     * Attach event listeners to modal
     */
    attachModalEventListeners() {
        // Close buttons
        this.modal.querySelectorAll('[data-close-browser]').forEach(btn => {
            btn.addEventListener('click', () => this.close());
        });
        
        // Click outside to close
        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal) {
                this.close();
            }
        });
        
        // Escape key to close
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen) {
                this.close();
            }
        });
        
        // Content type filter
        const contentTypeFilter = this.modal.querySelector('[data-content-type-filter]');
        contentTypeFilter.addEventListener('change', () => {
            const selectedId = parseInt(contentTypeFilter.value);
            this.currentContentType = this.contentTypes.find(ct => ct.id === selectedId);
            this.currentPage = 1;
            this.renderContent();
        });
        
        // Search
        const searchInput = this.modal.querySelector('[data-search-input]');
        const searchButton = this.modal.querySelector('[data-search-button]');
        
        searchButton.addEventListener('click', () => this.performSearch());
        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                this.performSearch();
            }
        });
        
        // Pagination
        this.modal.querySelector('[data-prev-page]').addEventListener('click', () => {
            if (this.currentPage > 1) {
                this.currentPage--;
                this.renderContent();
            }
        });
        
        this.modal.querySelector('[data-next-page]').addEventListener('click', () => {
            this.currentPage++;
            this.renderContent();
        });
        
        // Select content button
        this.modal.querySelector('[data-select-content]').addEventListener('click', () => {
            this.selectContent();
        });
    }
    
    /**
     * Render modal content
     */
    async renderContent() {
        this.renderContentTypeFilter();
        await this.loadAndRenderItems();
    }
    
    /**
     * Render content type filter dropdown
     */
    renderContentTypeFilter() {
        const filter = this.modal.querySelector('[data-content-type-filter]');
        
        const options = this.contentTypes.map(ct => 
            `<option value="${ct.id}" ${ct.id === this.currentContentType?.id ? 'selected' : ''}>
                ${this.escapeHtml(ct.name)} (${ct.content_count} items)
            </option>`
        ).join('');
        
        filter.innerHTML = options;
    }
    
    /**
     * Load and render content items
     */
    async loadAndRenderItems() {
        if (!this.currentContentType) {
            this.renderEmptyState('No content type selected');
            return;
        }
        
        this.showLoading();
        
        try {
            const options = {
                page: this.currentPage,
                limit: this.itemsPerPage,
                search: this.searchQuery,
                show_field_mappings: true
            };
            
            if (this.currentComponent?.type === 'widget') {
                options.widget_id = this.currentComponent.data.widget_id;
            }
            
            const response = await this.api.loadContentItems(
                this.pageId,
                this.currentContentType.id,
                options
            );
            
            if (response.success) {
                this.renderItems(response.data);
                this.renderPagination(response.data.pagination);
            } else {
                this.renderEmptyState('Failed to load content: ' + response.error);
            }
            
        } catch (error) {
            console.error('Error loading content items:', error);
            this.renderEmptyState('Error loading content');
        }
    }
    
    /**
     * Render content items
     */
    renderItems(data) {
        const container = this.modal.querySelector('.content-items-container');
        
        if (!data.items || data.items.length === 0) {
            this.renderEmptyState('No content items found');
            return;
        }
        
        const itemsHtml = `
            <div class="content-items-grid">
                ${data.items.map(item => this.renderContentItem(item)).join('')}
            </div>
        `;
        
        container.innerHTML = itemsHtml;
        
        // Attach item event listeners
        container.querySelectorAll('.content-item-card').forEach(card => {
            card.addEventListener('click', () => {
                this.toggleItemSelection(card);
            });
        });
    }
    
    /**
     * Render individual content item
     */
    renderContentItem(item) {
        const isSelected = this.selectedItems.some(selected => selected.id === item.id);
        
        return `
            <div class="content-item-card ${isSelected ? 'selected' : ''}" data-item-id="${item.id}">
                <div class="content-item-title">${this.escapeHtml(item.title)}</div>
                ${item.excerpt ? 
                    `<div class="content-item-excerpt">${this.escapeHtml(item.excerpt.substring(0, 120))}${item.excerpt.length > 120 ? '...' : ''}</div>` 
                    : ''
                }
                <div class="content-item-meta">
                    <div>Created: ${this.formatDate(item.created_at)}</div>
                    ${item.status ? `<div>Status: ${this.escapeHtml(item.status)}</div>` : ''}
                </div>
                ${isSelected ? '<div class="selected-indicator">✓</div>' : ''}
            </div>
        `;
    }
    
    /**
     * Toggle item selection
     */
    toggleItemSelection(card) {
        const itemId = parseInt(card.dataset.itemId);
        const existingIndex = this.selectedItems.findIndex(item => item.id === itemId);
        
        if (existingIndex !== -1) {
            // Deselect
            this.selectedItems.splice(existingIndex, 1);
            card.classList.remove('selected');
            card.querySelector('.selected-indicator')?.remove();
        } else {
            // Select - for now, only allow single selection
            // Clear previous selections
            this.selectedItems = [];
            this.modal.querySelectorAll('.content-item-card').forEach(c => {
                c.classList.remove('selected');
                c.querySelector('.selected-indicator')?.remove();
            });
            
            // Add new selection
            const container = this.modal.querySelector('.content-items-container');
            const itemsData = container.dataset.itemsData;
            
            // Find item data (you'd need to store this during rendering)
            // For now, create basic item data
            this.selectedItems.push({ id: itemId });
            card.classList.add('selected');
            card.insertAdjacentHTML('beforeend', '<div class="selected-indicator">✓</div>');
        }
        
        this.updateSelectionUI();
    }
    
    /**
     * Update selection UI
     */
    updateSelectionUI() {
        const countSpan = this.modal.querySelector('[data-selected-count]');
        const selectButton = this.modal.querySelector('[data-select-content]');
        
        countSpan.textContent = this.selectedItems.length;
        selectButton.disabled = this.selectedItems.length === 0;
    }
    
    /**
     * Render pagination
     */
    renderPagination(pagination) {
        const infoSpan = this.modal.querySelector('[data-pagination-info]');
        const prevButton = this.modal.querySelector('[data-prev-page]');
        const nextButton = this.modal.querySelector('[data-next-page]');
        
        if (pagination) {
            const start = ((pagination.current_page - 1) * pagination.per_page) + 1;
            const end = Math.min(pagination.current_page * pagination.per_page, pagination.total);
            
            infoSpan.textContent = `${start}-${end} of ${pagination.total} items`;
            
            prevButton.disabled = pagination.current_page <= 1;
            nextButton.disabled = !pagination.has_more;
        } else {
            infoSpan.textContent = '-';
            prevButton.disabled = true;
            nextButton.disabled = true;
        }
    }
    
    /**
     * Perform search
     */
    performSearch() {
        const searchInput = this.modal.querySelector('[data-search-input]');
        this.searchQuery = searchInput.value.trim();
        this.currentPage = 1;
        this.renderContent();
    }
    
    /**
     * Select content and close modal
     */
    selectContent() {
        if (this.selectedItems.length === 0) return;
        
        console.log('✅ Content selected:', this.selectedItems);
        
        // Trigger callback if set
        if (this.onContentSelect) {
            this.onContentSelect(this.selectedItems, this.currentComponent);
        }
        
        this.close();
    }
    
    /**
     * Set content selection callback
     */
    setContentSelectCallback(callback) {
        this.onContentSelect = callback;
    }
    
    /**
     * Show loading state
     */
    showLoading() {
        const container = this.modal.querySelector('.content-items-container');
        container.innerHTML = `
            <div class="component-loading">
                <div class="loading-spinner"></div>
                <p>Loading content items...</p>
            </div>
        `;
    }
    
    /**
     * Render empty state
     */
    renderEmptyState(message) {
        const container = this.modal.querySelector('.content-items-container');
        container.innerHTML = `
            <div class="component-loading">
                <i class="ri-file-line" style="font-size: 2rem; color: #6c757d;"></i>
                <p>${this.escapeHtml(message)}</p>
            </div>
        `;
    }
    
    /**
     * Format date for display
     */
    formatDate(dateString) {
        try {
            return new Date(dateString).toLocaleDateString();
        } catch {
            return dateString;
        }
    }
    
    /**
     * Escape HTML to prevent XSS
     */
    escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text.toString();
        return div.innerHTML;
    }
}

// Export for global use
window.ContentBrowser = ContentBrowser;

console.log('📦 ContentBrowser module loaded');