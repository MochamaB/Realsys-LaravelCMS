/**
 * Page Builder Page Actions Handler
 * 
 * Handles all page-related actions from the iframe preview
 */
class PageBuilderPageActions {
    constructor() {
        console.log('📄 Page Builder Page Actions initialized');
    }

    /**
     * Handle page edit request from iframe
     * Opens page settings modal or redirects to page edit
     */
    handlePageEdit(data, event) {
        console.log('✏️ Handling page edit request:', data);

        try {
            const { pageId, pageTitle, template } = data;

            if (!pageId) {
                console.warn('⚠️ Page ID not provided for edit request');
                return;
            }

            console.log(`📝 Edit page: ${pageTitle || 'Unknown'} (ID: ${pageId}, Template: ${template || 'Unknown'})`);

            // For now, just redirect to page edit route
            this.redirectToPageEdit(pageId);

        } catch (error) {
            console.error('❌ Error handling page edit:', error);
            this.showError('Failed to open page editor');
        }
    }

    /**
     * Redirect to page edit route (fallback)
     */
    redirectToPageEdit(pageId) {
        try {
            // Construct edit URL (adjust route as needed)
            const editUrl = `/admin/pages/${pageId}/edit`;
            
            // Confirm with user before leaving Page Builder
            const confirmLeave = confirm(
                'This will take you to the full page editor and you will lose any unsaved changes in Page Builder.\n\n' +
                'Do you want to continue?'
            );

            if (confirmLeave) {
                window.location.href = editUrl;
            }

        } catch (error) {
            console.error('❌ Error redirecting to page edit:', error);
            this.showError('Failed to open page editor');
        }
    }

    /**
     * Handle page selection from iframe (for UI updates)
     */
    handlePageSelected(data, event) {
        console.log('🎯 Page selected:', data);

        try {
            const { pageId, pageTitle, template } = data;

            // Update parent UI to reflect page selection
            this.updatePageSelectionUI(pageId, pageTitle, template);

            // Could also update browser title, toolbar info, etc.
            this.updateBrowserTitle(pageTitle);

        } catch (error) {
            console.error('❌ Error handling page selection:', error);
        }
    }

    /**
     * Handle page deselection from iframe
     */
    handlePageDeselected(data, event) {
        console.log('🎯 Page deselected');

        try {
            // Clear page selection UI
            this.clearPageSelectionUI();

        } catch (error) {
            console.error('❌ Error handling page deselection:', error);
        }
    }

    /**
     * Update UI to reflect page selection
     */
    updatePageSelectionUI(pageId, pageTitle, template) {
        // Add visual indicators that page is selected
        document.body.classList.add('page-selected');
        
        // Update page info display if it exists
        const pageInfoDisplay = document.getElementById('page-info-display');
        if (pageInfoDisplay) {
            pageInfoDisplay.innerHTML = `
                <div class="d-flex align-items-center gap-2 text-primary">
                    <i class="ri-file-text-line"></i>
                    <span class="fw-medium">${pageTitle || 'Untitled Page'}</span>
                    <span class="text-muted">|</span>
                    <span class="small">${template || 'Unknown Template'}</span>
                    <span class="badge bg-primary ms-2">Selected</span>
                </div>
            `;
        }

        console.log('📄 Page selection UI updated');
    }

    /**
     * Clear page selection UI
     */
    clearPageSelectionUI() {
        // Remove visual indicators
        document.body.classList.remove('page-selected');
        
        // Clear page info display
        const pageInfoDisplay = document.getElementById('page-info-display');
        if (pageInfoDisplay) {
            pageInfoDisplay.innerHTML = `
                <div class="d-flex align-items-center gap-2 text-muted">
                    <span class="small">No page selected</span>
                </div>
            `;
        }

        console.log('📄 Page selection UI cleared');
    }

    /**
     * Update browser title to reflect current page
     */
    updateBrowserTitle(pageTitle) {
        if (pageTitle) {
            document.title = `Page Builder - ${pageTitle}`;
        }
    }

    /**
     * Handle save page request (if needed)
     */
    handlePageSave(data, event) {
        console.log('💾 Handling page save request:', data);

        try {
            // TODO: Implement page saving logic
            // This might involve collecting all changes and sending to server
            
            this.showInfo('Page saving will be implemented soon');

        } catch (error) {
            console.error('❌ Error handling page save:', error);
            this.showError('Failed to save page');
        }
    }

    /**
     * Handle page preview request
     */
    handlePagePreview(data, event) {
        console.log('👁️ Handling page preview request:', data);

        try {
            const { pageId } = data;

            if (!pageId) {
                console.warn('⚠️ Page ID not provided for preview request');
                return;
            }

            // Open page preview in new tab
            const previewUrl = `/admin/pages/${pageId}/preview`;
            window.open(previewUrl, '_blank');

            console.log('✅ Page preview opened');

        } catch (error) {
            console.error('❌ Error opening page preview:', error);
            this.showError('Failed to open page preview');
        }
    }

    /**
     * Show success message to user
     */
    showSuccess(message) {
        // TODO: Implement proper notification system
        console.log('✅ Success:', message);
    }

    /**
     * Show error message to user
     */
    showError(message) {
        // TODO: Implement proper notification system
        console.error('❌ Error:', message);
        alert('Error: ' + message); // Temporary fallback
    }

    /**
     * Show info message to user
     */
    showInfo(message) {
        // TODO: Implement proper notification system
        console.log('ℹ️ Info:', message);
        alert('Info: ' + message); // Temporary fallback
    }
}

// Export for use by main coordinator
window.PageBuilderPageActions = PageBuilderPageActions;

console.log('📄 Page Builder Page Actions script loaded');