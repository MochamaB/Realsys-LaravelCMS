/**
 * Page Preview Module
 * 
 * Handles page-level interactions, selection, hover detection, and toolbar functionality
 * within the preview iframe environment
 */

(function() {
    'use strict';

    // Page Preview Module
    const PagePreview = {
        currentSelection: {
            level: 'none', // 'none', 'page', 'page-selected'
            element: null,
            data: null
        },

        // Initialize page preview functionality
        init: function(sharedState) {
            this.sharedState = sharedState;
            this.initializePageSelection();
            console.log('🏗️ Page preview module initialized');
        },

        // Initialize page selection system
        initializePageSelection: function() {
            console.log('🏗️ Initializing page selection system');
            
            // Setup page interaction using proper data attributes
            this.setupPageInteractions();
            
            // Add escape key to deselect
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    this.deselectPage();
                }
            });
            
            console.log('✅ Page selection system initialized');
        },

        // Setup page interactions using proper data attributes
        setupPageInteractions: function() {
            const pageElement = document.querySelector('[data-preview-page]');
            
            if (pageElement) {
                // Make page element interactive
                pageElement.style.cursor = 'pointer';
                pageElement.setAttribute('tabindex', '0');
                
                // Hover handler
                pageElement.addEventListener('mouseenter', (e) => {
                    if (this.currentSelection.level !== 'page-selected') {
                        this.activatePageHover();
                    }
                });
                
                pageElement.addEventListener('mouseleave', (e) => {
                    if (this.currentSelection.level === 'page') {
                        this.deactivatePageHover();
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
                
                console.log('✅ Page interactions setup for element:', pageElement);
            } else {
                console.warn('⚠️ No page element found with [data-preview-page] attribute');
            }
        },


        // Activate page hover state
        activatePageHover: function() {
            console.log('🎯 Page hover activated');
            
            const pageElement = document.querySelector('[data-preview-page]');
            
            this.currentSelection.level = 'page';
            this.currentSelection.element = pageElement;
            this.currentSelection.data = {
                id: pageElement.getAttribute('data-preview-page'),
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
            console.log('🎯 Page hover deactivated');
            
            if (this.currentSelection.level === 'page') {
                const pageElement = document.querySelector('[data-preview-page]');
                
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

        // Show page selection indicator (now handled by CSS ::before pseudo-element)
        showPageSelectionIndicator: function() {
            // CSS handles the indicator display automatically via ::before pseudo-element
            // No JavaScript needed - the CSS shows page title and template from data attributes
            console.log('📄 Page selection indicator shown via CSS');
        },

        // Hide page selection indicator (now handled by CSS)
        hidePageSelectionIndicator: function() {
            // CSS handles hiding automatically when preview-highlighted class is removed
            console.log('📄 Page selection indicator hidden via CSS');
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
            console.log('🎯 Page selection activated');
            
            const pageElement = document.querySelector('[data-preview-page]');
            
            this.currentSelection.level = 'page-selected';
            
            // Ensure page has preview-highlighted class for CSS styling
            if (pageElement) {
                pageElement.classList.add('preview-highlighted');
            }
            
            // Show page toolbar buttons
            this.showPageToolbar();
            
            // Initialize section sorting
            if (window.SectionPreview) {
                window.SectionPreview.initializeSectionSorting();
            }
            
            // Keep section highlights
            this.highlightSortableSections();
        },

        // Show page toolbar (using CSS classes)
        showPageToolbar: function() {
            const pageElement = document.querySelector('[data-preview-page]');
            if (!pageElement) return;
            
            // Remove existing toolbar buttons
            const existing = pageElement.querySelector('.page-toolbar-buttons');
            if (existing) existing.remove();
            
            // Create toolbar buttons container
            const buttonsContainer = document.createElement('div');
            buttonsContainer.className = 'page-toolbar-buttons';
            
            // Edit button
            const editBtn = document.createElement('button');
            editBtn.className = 'page-toolbar-btn btn-info';
            editBtn.innerHTML = '<i class="ri-pencil-fill"></i> Edit';
            editBtn.title = 'Edit Page Settings';
            editBtn.addEventListener('click', (e) => this.handlePageEdit(e));
            
            // Delete button
            const deleteBtn = document.createElement('button');
            deleteBtn.className = 'page-toolbar-btn btn-info';
            deleteBtn.innerHTML = '<i class="ri-delete-bin-fill"></i> Delete';
            deleteBtn.title = 'Delete Page';
            deleteBtn.addEventListener('click', (e) => this.handlePageDelete(e));
            
            // Close button
            const closeBtn = document.createElement('button');
            closeBtn.className = 'page-toolbar-btn btn-info';
            closeBtn.innerHTML = '<i class="ri-close-line"></i>';
            closeBtn.title = 'Close Page Selection';
            closeBtn.addEventListener('click', (e) => this.deselectPage(e));
            
            // Assemble toolbar
            buttonsContainer.appendChild(editBtn);
            buttonsContainer.appendChild(deleteBtn);
            buttonsContainer.appendChild(closeBtn);
            
            // Add to page element (CSS positions it correctly)
            pageElement.appendChild(buttonsContainer);
            
            console.log('✅ Page toolbar created with CSS classes');
        },

        // Handle page edit action
        handlePageEdit: function(e) {
            console.log('🔧 Page edit clicked');
            
            // Communicate with parent window
            if (window.parent) {
                window.parent.postMessage({
                    type: 'page-edit-requested',
                    data: {
                        pageId: this.currentSelection.data?.id,
                        pageTitle: this.currentSelection.data?.title,
                        template: this.currentSelection.data?.template
                    }
                }, '*');
            }
        },

        // Handle page delete action
        handlePageDelete: function() {
            console.log('🗑️ Page delete clicked');
            
            const pageTitle = this.currentSelection.data?.title || 'this page';
            
            if (confirm(`Are you sure you want to delete "${pageTitle}"? This action cannot be undone.`)) {
                // Communicate with parent window
                if (window.parent) {
                    window.parent.postMessage({
                        type: 'page-delete-requested',
                        data: {
                            pageId: this.currentSelection.data?.id,
                            pageTitle: this.currentSelection.data?.title
                        }
                    }, '*');
                }
            }
        },

        // Deselect page
        deselectPage: function() {
            console.log('🎯 Page deselected');
            
            const pageElement = document.querySelector('[data-preview-page]');
            
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
            
            // Destroy section sorting if it exists
            if (window.sectionSortable) {
                window.sectionSortable.destroy();
                window.sectionSortable = null;
                console.log('🗑️ Section sorting destroyed');
            }
        },

        // Handle zoom changes from parent window
        handleZoomChange: function(newZoom) {
            console.log('📏 Zoom changed to:', newZoom);
            
            // Update all section toolbars
            document.querySelectorAll('[data-preview-section]').forEach(section => {
                if (window.SectionPreview) {
                    window.SectionPreview.updateToolbarZoomScale(section);
                }
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
    window.PagePreview = PagePreview;

})();
