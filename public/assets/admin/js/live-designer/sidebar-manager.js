/**
 * Sidebar Manager for Live Designer
 * Handles sidebar collapse/expand functionality via header buttons only
 */
class SidebarManager {
    constructor() {
        this.leftSidebarContainer = document.getElementById('leftSidebarContainer');
        this.rightSidebarContainer = document.getElementById('right-sidebar-container');
        this.leftToggleBtn = document.getElementById('sidebarToggleBtn');
        this.rightToggleBtn = document.getElementById('rightSidebarToggleBtn');
        
        this.init();
    }
    
    /**
     * Initialize sidebar manager
     */
    init() {
        console.log('🎛️ SidebarManager initializing...');
        
        // Setup left sidebar toggle with retry
        this.setupLeftSidebarToggle();
        
        // Setup right sidebar toggle with retry
        this.setupRightSidebarToggle();
        
        console.log('✅ SidebarManager initialized');
    }
    
    /**
     * Setup left sidebar toggle functionality
     */
    setupLeftSidebarToggle() {
        if (!this.leftToggleBtn || !this.leftSidebarContainer) {
            // Retry after delay if elements not found
            setTimeout(() => {
                this.leftToggleBtn = document.getElementById('sidebarToggleBtn');
                this.leftSidebarContainer = document.getElementById('leftSidebarContainer');
                if (this.leftToggleBtn && this.leftSidebarContainer) {
                    this.attachLeftSidebarEvents();
                } else {
                    console.warn('⚠️ Left sidebar elements not found after retry');
                }
            }, 1000);
            return;
        }
        
        this.attachLeftSidebarEvents();
    }
    
    /**
     * Attach left sidebar event listeners
     */
    attachLeftSidebarEvents() {
        this.leftToggleBtn.addEventListener('click', (e) => {
            e.preventDefault();
            this.toggleLeftSidebar();
        });
        console.log('✅ Left sidebar toggle setup complete');
    }
    
    /**
     * Setup right sidebar toggle functionality
     */
    setupRightSidebarToggle() {
        if (!this.rightToggleBtn || !this.rightSidebarContainer) {
            // Retry after delay if elements not found
            setTimeout(() => {
                this.rightToggleBtn = document.getElementById('rightSidebarToggleBtn');
                this.rightSidebarContainer = document.getElementById('right-sidebar-container');
                if (this.rightToggleBtn && this.rightSidebarContainer) {
                    this.attachRightSidebarEvents();
                } else {
                    console.warn('⚠️ Right sidebar elements not found after retry');
                }
            }, 1000);
            return;
        }
        
        this.attachRightSidebarEvents();
    }
    
    /**
     * Attach right sidebar event listeners
     */
    attachRightSidebarEvents() {
        this.rightToggleBtn.addEventListener('click', (e) => {
            e.preventDefault();
            this.toggleRightSidebar();
        });
        
        // Set initial icon state
        this.updateRightSidebarIcon();
        console.log('✅ Right sidebar toggle setup complete');
    }
    
    /**
     * Toggle left sidebar collapsed state
     */
    toggleLeftSidebar() {
        if (!this.leftSidebarContainer) return;
        
        this.leftSidebarContainer.classList.toggle('collapsed');
        this.updateLeftSidebarIcon();
        
        console.log('📋 Left sidebar toggled:', 
            this.leftSidebarContainer.classList.contains('collapsed') ? 'collapsed' : 'expanded');
    }
    
    /**
     * Toggle right sidebar collapsed state
     */
    toggleRightSidebar() {
        if (!this.rightSidebarContainer) return;
        
        this.rightSidebarContainer.classList.toggle('collapsed');
        this.updateRightSidebarIcon();
        
        console.log('🔧 Right sidebar toggled:', 
            this.rightSidebarContainer.classList.contains('collapsed') ? 'collapsed' : 'expanded');
    }
    
    /**
     * Update left sidebar toggle button icon
     */
    updateLeftSidebarIcon() {
        if (!this.leftToggleBtn) return;
        
        const icon = this.leftToggleBtn.querySelector('i');
        if (!icon) return;
        
        if (this.leftSidebarContainer.classList.contains('collapsed')) {
            icon.className = 'ri-arrow-right-line';
        } else {
            icon.className = 'ri-arrow-left-line';
        }
    }
    
    /**
     * Update right sidebar toggle button icon
     */
    updateRightSidebarIcon() {
        if (!this.rightToggleBtn) return;
        
        const icon = this.rightToggleBtn.querySelector('i');
        if (!icon) return;
        
        if (this.rightSidebarContainer.classList.contains('collapsed')) {
            icon.className = 'ri-arrow-left-line';
        } else {
            icon.className = 'ri-arrow-right-line';
        }
    }
    
    /**
     * Get current sidebar states
     */
    getSidebarStates() {
        return {
            leftCollapsed: this.leftSidebarContainer?.classList.contains('collapsed') || false,
            rightCollapsed: this.rightSidebarContainer?.classList.contains('collapsed') || false
        };
    }
    
    /**
     * Set sidebar states programmatically
     */
    setSidebarStates(states) {
        if (states.leftCollapsed !== undefined) {
            const isCurrentlyCollapsed = this.leftSidebarContainer?.classList.contains('collapsed');
            if (states.leftCollapsed !== isCurrentlyCollapsed) {
                this.toggleLeftSidebar();
            }
        }
        
        if (states.rightCollapsed !== undefined) {
            const isCurrentlyCollapsed = this.rightSidebarContainer?.classList.contains('collapsed');
            if (states.rightCollapsed !== isCurrentlyCollapsed) {
                this.toggleRightSidebar();
            }
        }
    }
}

// Initialize sidebar manager when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.sidebarManager = new SidebarManager();
});

// Export for use in other modules
window.SidebarManager = SidebarManager;