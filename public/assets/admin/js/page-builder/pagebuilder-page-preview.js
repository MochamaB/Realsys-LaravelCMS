/**
 * Page Builder Page Preview Module
 * 
 * Handles page-level interactions, selection, hover detection, and toolbar functionality
 * within the Page Builder preview iframe environment
 * 
 * Based on Live Designer page-preview.js pattern
 */

(function() {
    'use strict';

    // Page Builder Page Preview Module
    const PageBuilderPagePreview = {
        isInitialized: false, // Prevent duplicate initialization
        currentSelection: {
            level: 'none', // 'none', 'page', 'page-selected'
            element: null,
            data: null
        },

        // Initialize page preview functionality
        init: function(sharedState) {
            if (this.isInitialized) {
                console.warn('⚠️ Page Builder page preview already initialized - skipping');
                return;
            }
            
            this.sharedState = sharedState;
            this.initializePageSelection();
            this.isInitialized = true;
            console.log('🏗️ Page Builder page preview module initialized');
        },

        // Initialize page selection system
        initializePageSelection: function() {
            console.log('🏗️ Initializing Page Builder page selection system');
            
            // Setup page interaction using proper data attributes
            this.setupPageInteractions();
            
            // Add escape key to deselect
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    this.deselectPage();
                }
            });
            
            console.log('✅ Page Builder page selection system initialized');
        },

        // Setup page interactions using proper data attributes
        setupPageInteractions: function() {
            const pageElement = document.querySelector('[data-pagebuilder-page]');
            
            if (pageElement) {
                // Make page element interactive
                pageElement.style.cursor = 'pointer';
                pageElement.setAttribute('tabindex', '0');
                
                // Simple hover handler - only trigger on page container edges/empty areas
                let isHovering = false;
                
                pageElement.addEventListener('mouseenter', (e) => {
                    if (!isHovering && this.currentSelection.level !== 'page-selected') {
                        isHovering = true;
                        this.activatePageHover();
                    }
                });
                
                pageElement.addEventListener('mouseleave', (e) => {
                    // Only deactivate if mouse is actually leaving the page container
                    const rect = pageElement.getBoundingClientRect();
                    const x = e.clientX;
                    const y = e.clientY;
                    
                    // Check if mouse is outside the page container bounds
                    if (x < rect.left || x > rect.right || y < rect.top || y > rect.bottom) {
                        if (isHovering && this.currentSelection.level === 'page') {
                            isHovering = false;
                            this.deactivatePageHover();
                        }
                    }
                });
                
                // Click handler
                pageElement.addEventListener('click', (e) => {
                    // Check if click is not on a specific widget or section
                    const isOnWidget = e.target.closest('[data-preview-widget]');
                    const isOnSection = e.target.closest('[data-preview-section]');
                    
                    if (!isOnWidget && !isOnSection) {
                        e.preventDefault();
                        e.stopPropagation();
                        
                        console.log('🎯 Page clicked - activating page selection');
                        this.activatePageSelection();
                    }
                });
                
                // Keyboard handler
                pageElement.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        this.activatePageSelection();
                    }
                });
                
                console.log('✅ Page Builder page interactions setup for element:', pageElement);
            } else {
                console.warn('⚠️ No page element found with [data-pagebuilder-page] attribute');
            }
        },

        // Activate page hover state
        activatePageHover: function() {
            // Only log if state actually changes
            if (this.currentSelection.level !== 'page') {
                console.log('🎯 Page Builder page hover activated');
            }
            
            const pageElement = document.querySelector('[data-pagebuilder-page]');
            
            this.currentSelection.level = 'page';
            this.currentSelection.element = pageElement;
            this.currentSelection.data = {
                id: pageElement.getAttribute('data-pagebuilder-page'),
                title: pageElement.getAttribute('data-page-title'),
                template: pageElement.getAttribute('data-page-template')
            };
            
            // Add CSS class for hover state (CSS handles styling)
            pageElement.classList.add('preview-highlighted');
            
            // Highlight all sections as sortable
            this.highlightSortableSections();
        },

        // Deactivate page hover state
        deactivatePageHover: function() {
            // Only log if state actually changes
            if (this.currentSelection.level === 'page') {
                console.log('🎯 Page Builder page hover deactivated');
            }
            
            if (this.currentSelection.level === 'page') {
                const pageElement = document.querySelector('[data-pagebuilder-page]');
                
                this.currentSelection.level = 'none';
                this.currentSelection.element = null;
                this.currentSelection.data = null;
                
                // Remove CSS class (CSS handles styling removal)
                if (pageElement) {
                    pageElement.classList.remove('preview-highlighted');
                }
                
                // Remove section highlights
                this.removeAllHighlights();
            }
        },

        // Highlight all sections as sortable
        highlightSortableSections: function() {
            const sections = document.querySelectorAll('[data-preview-type="section"]');
            sections.forEach(section => {
                section.style.outline = '1px dashed rgba(0, 123, 255, 0.5)';
                section.style.outlineOffset = '2px';
                section.style.backgroundColor = 'rgba(0, 123, 255, 0.05)';
            });
        },

        // Remove all highlights
        removeAllHighlights: function() {
            const sections = document.querySelectorAll('[data-preview-type="section"]');
            sections.forEach(section => {
                section.style.outline = '';
                section.style.outlineOffset = '';
                section.style.backgroundColor = '';
            });
        },

        // Activate full page selection (clicked state)
        activatePageSelection: function() {
            console.log('🎯 Page Builder page selection activated');
            
            const pageElement = document.querySelector('[data-pagebuilder-page]');
            
            this.currentSelection.level = 'page-selected';
            
            // Ensure page has preview-highlighted class for CSS styling
            if (pageElement) {
                pageElement.classList.add('preview-highlighted');
            }
            
            // Show page toolbar buttons
            this.showPageToolbar();
            
            // Keep section highlights
            this.highlightSortableSections();
            
            // Notify parent window using central message sender
            if (window.pageBuilderMessageSender) {
                window.pageBuilderMessageSender.page.selected({
                    pageId: this.currentSelection.data?.id,
                    pageTitle: this.currentSelection.data?.title,
                    template: this.currentSelection.data?.template
                });
            }
        },

        // Show page toolbar (using CSS classes)
        showPageToolbar: function() {
            const pageElement = document.querySelector('[data-pagebuilder-page]');
            if (!pageElement) return;
            
            // Remove existing toolbar buttons
            const existing = pageElement.querySelector('.page-toolbar-buttons');
            if (existing) existing.remove();
            
            // Create toolbar buttons container
            const buttonsContainer = document.createElement('div');
            buttonsContainer.className = 'page-toolbar-buttons';
            
            // Page info section (left side)
            const pageInfoSection = document.createElement('div');
            pageInfoSection.className = 'page-toolbar-info d-flex align-items-center';
            
            const pageTitle = this.currentSelection.data?.title || 'Untitled Page';
            const pageTemplate = this.currentSelection.data?.template || 'Unknown Template';
            
            pageInfoSection.innerHTML = `
                <div class="d-flex align-items-center gap-2">
                    <i class="ri-file-text-line text-primary"></i>
                    <span class="fw-bold text-dark">${pageTitle}</span>
                    <span class="text-muted">|</span>
                    <span class="small text-muted">${pageTemplate}</span>
                </div>
            `;
            
            // Action buttons section (right side)
            const actionsSection = document.createElement('div');
            actionsSection.className = 'page-toolbar-actions d-flex align-items-center gap-2';
            
            // Edit button
            const editBtn = document.createElement('button');
            editBtn.className = 'page-toolbar-btn btn-info btn-sm';
            editBtn.innerHTML = '<i class="ri-pencil-fill"></i> Edit Page';
            editBtn.title = 'Edit Page Settings';
            editBtn.addEventListener('click', (e) => this.handlePageEdit(e));
            
            // Add Section button
            const addSectionBtn = document.createElement('button');
            addSectionBtn.className = 'page-toolbar-btn btn-success btn-sm';
            addSectionBtn.innerHTML = '<i class="ri-add-box-fill"></i> Add Section';
            addSectionBtn.title = 'Add New Section';
            addSectionBtn.addEventListener('click', (e) => this.handleAddSection(e));
            
            // Close button
            const closeBtn = document.createElement('button');
            closeBtn.className = 'page-toolbar-btn btn-secondary btn-sm';
            closeBtn.innerHTML = '<i class="ri-close-line"></i>';
            closeBtn.title = 'Close Page Selection';
            closeBtn.addEventListener('click', (e) => this.deselectPage(e));
            
            // Assemble actions section
            actionsSection.appendChild(editBtn);
            actionsSection.appendChild(addSectionBtn);
            actionsSection.appendChild(closeBtn);
            
            // Assemble toolbar with flexbox layout
            buttonsContainer.appendChild(pageInfoSection);
            buttonsContainer.appendChild(actionsSection);
            
            // Add to page element (CSS positions it correctly)
            pageElement.appendChild(buttonsContainer);
            
            console.log('✅ Page Builder page toolbar created with page info');
        },

        // Handle page edit action
        handlePageEdit: function(e) {
            console.log('🔧 Page Builder page edit clicked');
            
            // Use central message sender
            if (window.pageBuilderMessageSender) {
                window.pageBuilderMessageSender.page.editRequested({
                    pageId: this.currentSelection.data?.id,
                    pageTitle: this.currentSelection.data?.title,
                    template: this.currentSelection.data?.template
                });
            }
        },

        // Handle add section action
        handleAddSection: function(e) {
            console.log('➕ Page Builder add section clicked');
            
            // Use central message sender
            if (window.pageBuilderMessageSender) {
                window.pageBuilderMessageSender.page.addSectionRequested({
                    pageId: this.currentSelection.data?.id,
                    pageTitle: this.currentSelection.data?.title
                });
            }
        },

        // Deselect page
        deselectPage: function() {
            console.log('🎯 Page Builder page deselected');
            
            const pageElement = document.querySelector('[data-pagebuilder-page]');
            
            this.currentSelection.level = 'none';
            this.currentSelection.element = null;
            this.currentSelection.data = null;
            
            // Remove CSS class and toolbar buttons
            if (pageElement) {
                pageElement.classList.remove('preview-highlighted');
                const toolbar = pageElement.querySelector('.page-toolbar-buttons');
                if (toolbar) toolbar.remove();
            }
            
            // Remove section highlights
            this.removeAllHighlights();
            
            // Notify parent window using central message sender
            if (window.pageBuilderMessageSender) {
                window.pageBuilderMessageSender.page.deselected();
            }
        },

        // Handle zoom changes from parent window
        handleZoomChange: function(newZoom) {
            console.log('📏 Zoom changed to:', newZoom);
            
            // Counter-scale toolbar to maintain original size against parent zoom
            document.querySelectorAll('.page-toolbar-buttons').forEach(toolbar => {
                // Apply inverse scaling to counteract parent zoom
                const counterScale = 1 / newZoom;
                toolbar.style.transform = `scale(${counterScale})`;
                toolbar.style.transformOrigin = 'top left';
            });
        },

        // Get current selection info
        getCurrentSelection: function() {
            return this.currentSelection;
        },

        // Check if page is currently selected
        isPageSelected: function() {
            return this.currentSelection.level === 'page-selected';
        }
    };

    // Export to global scope for main coordinator
    window.PageBuilderPagePreview = PageBuilderPagePreview;

})();