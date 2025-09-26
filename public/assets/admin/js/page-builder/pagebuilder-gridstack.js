/**
 * Page Builder GridStack Integration
 *
 * Phase 1: Page-level grid with sections as sortable items
 * Uses existing GridStack data attributes from sections
 */

class PageBuilderGridStack {
    constructor() {
        this.pageGrid = null;
        this.isInitialized = false;

        console.log('🎯 Page Builder GridStack initialized');
    }

    /**
     * Initialize Page-level GridStack (Phase 1)
     */
    initPageGrid() {
        const pageContainer = document.getElementById('pageGrid');

        if (!pageContainer) {
            console.warn('⚠️ Page grid container not found');
            return null;
        }

        console.log('🏗️ Initializing page-level GridStack...');

        // Debug: Check if we have grid items before initialization
        const existingItems = pageContainer.querySelectorAll('.grid-stack-item');
        console.log(`🔍 Found ${existingItems.length} existing grid-stack-item elements`);

        try {
            this.pageGrid = GridStack.init({
                cellHeight: 'auto',
                acceptWidgets: false,
                removable: false,
                resizable: false,
                handle: '.pagebuilder-section-toolbar [data-action="move-section"]',
                column: 12,
                margin: 10,
                float: false, // Prevent floating for predictable layout
                animate: true,
                minRow: 1
            }, pageContainer);

            // Handle section reordering
            this.pageGrid.on('change', (event, items) => {
                console.log('📦 Sections reordered:', items);
                this.updateSectionOrder(items);
            });

            // Handle drag start/stop for visual feedback
            this.pageGrid.on('dragstart', (event, el) => {
                console.log('🎯 Section drag started');
                el.classList.add('grid-stack-dragging');
            });

            this.pageGrid.on('dragstop', (event, el) => {
                console.log('🎯 Section drag stopped');
                el.classList.remove('grid-stack-dragging');
            });

            console.log('✅ Page-level GridStack initialized successfully');
            this.isInitialized = true;

            return this.pageGrid;

        } catch (error) {
            console.error('❌ Failed to initialize page GridStack:', error);
            return null;
        }
    }

    /**
     * Update section order via API
     */
    updateSectionOrder(items) {
        if (!items || items.length === 0) {
            console.log('📦 No items to reorder');
            return;
        }

        const orderData = items.map((item, index) => {
            // Extract section ID from grid-stack-id or data attributes
            const element = item.el || item;
            const sectionId = this.extractSectionId(element);

            return {
                section_id: sectionId,
                gs_x: item.x || 0,
                gs_y: item.y || index,
                gs_w: item.w || 12,
                gs_h: item.h || 1,
                gs_id: item.id,
                sort_order: index
            };
        }).filter(item => item.section_id); // Remove items without section ID

        console.log('📤 Sending section order update:', orderData);

        // Send to backend API
        this.sendSectionOrderUpdate(orderData);
    }

    /**
     * Extract section ID from grid item element
     */
    extractSectionId(element) {
        // Look for section element inside grid-stack-item-content
        const sectionElement = element.querySelector('section[data-pagebuilder-section]');
        if (sectionElement) {
            return sectionElement.getAttribute('data-pagebuilder-section') ||
                   sectionElement.getAttribute('data-section-id');
        }

        // Fallback: try to extract from grid-stack-id
        const gsId = element.getAttribute('data-gs-id');
        if (gsId && gsId.includes('section_')) {
            // Extract section ID from format like "section_1753641046_688670566ea19_1"
            const parts = gsId.split('_');
            if (parts.length >= 4) {
                return parts[3]; // Last part should be section ID
            }
        }

        console.warn('⚠️ Could not extract section ID from element:', element);
        return null;
    }

    /**
     * Send section order update to backend
     */
    async sendSectionOrderUpdate(orderData) {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            if (!csrfToken) {
                console.warn('⚠️ CSRF token not found - cannot send update');
                return;
            }

            // Get page ID from the page container
            const pageContainer = document.getElementById('pageGrid');
            const pageId = pageContainer?.getAttribute('data-pagebuilder-page');

            if (!pageId) {
                console.warn('⚠️ Page ID not found - cannot send update');
                return;
            }

            const response = await fetch(`/admin/api/pages/${pageId}/sections/reorder`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ sections: orderData })
            });

            if (response.ok) {
                const result = await response.json();
                console.log('✅ Section order updated successfully:', result);

                // Show success feedback to parent window via communicator
                if (window.pageBuilderIframeCommunicator) {
                    window.pageBuilderIframeCommunicator.notifySectionReordered(orderData);
                }
            } else {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

        } catch (error) {
            console.error('❌ Failed to update section order:', error);

            // Show error feedback to parent window via communicator
            if (window.pageBuilderIframeCommunicator) {
                window.pageBuilderIframeCommunicator.notifySectionReorderError(error);
            }
        }
    }

    // Note: sendMessageToParent method removed - now using PageBuilderIframeCommunicator

    /**
     * Get current grid state
     */
    getGridState() {
        if (!this.pageGrid) {
            return null;
        }

        return {
            isInitialized: this.isInitialized,
            itemCount: this.pageGrid.getGridItems().length,
            engine: this.pageGrid.engine
        };
    }

    /**
     * Refresh grid layout
     */
    refreshGrid() {
        if (this.pageGrid) {
            this.pageGrid.batchUpdate();
            this.pageGrid.compact();
            this.pageGrid.commit();
            console.log('🔄 Grid layout refreshed');
        }
    }

    /**
     * Destroy GridStack instance
     */
    destroy() {
        if (this.pageGrid) {
            this.pageGrid.destroy();
            this.pageGrid = null;
            this.isInitialized = false;
            console.log('🗑️ GridStack destroyed');
        }
    }
}

// Global instance
let pageBuilderGridStack = null;

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Initializing Page Builder GridStack...');

    // Check if we're in the correct environment
    if (!window.GridStack) {
        console.error('❌ GridStack library not loaded');
        return;
    }

    if (!document.getElementById('pageGrid')) {
        console.warn('⚠️ Page grid container not found - not a page builder page?');
        return;
    }

    // Create and initialize GridStack
    pageBuilderGridStack = new PageBuilderGridStack();
    const grid = pageBuilderGridStack.initPageGrid();

    if (grid) {
        console.log('✅ Page Builder GridStack ready for section sorting');

        // Expose to global scope for debugging
        window.pageBuilderGridStack = pageBuilderGridStack;

        // Notify parent window that GridStack is ready via communicator
        if (window.pageBuilderIframeCommunicator) {
            window.pageBuilderIframeCommunicator.notifyGridStackReady({
                phase: 1,
                sectionsCount: grid.getGridItems().length
            });
            console.log('📤 GridStack ready message sent via communicator');
        } else {
            console.warn('⚠️ pageBuilderIframeCommunicator not available for GridStack ready notification');
        }
    }
});

// Handle page unload
window.addEventListener('beforeunload', function() {
    if (pageBuilderGridStack) {
        pageBuilderGridStack.destroy();
    }
});

console.log('📦 Page Builder GridStack module loaded');