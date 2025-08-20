/**
 * Sidebar Manager for Live Designer
 * Handles sidebar interactions and state management
 */
class SidebarManager {
    constructor(editor) {
        this.editor = editor;
        this.leftSidebar = document.getElementById('left-sidebar');
        this.rightSidebar = document.getElementById('right-sidebar');
        this.canvasArea = document.getElementById('canvas-area');
        this.selectedComponent = null;
        this.rightSidebarManuallyToggled = false;
        
        this.initializeEventListeners();
        this.initializeEditorEvents();
        this.initializeAutoCollapse();
        console.log('🎛️ SidebarManager initialized');
    }
    
    /**
     * Initialize event listeners for sidebar controls
     */
    initializeEventListeners() {
        // Left sidebar toggle
        const leftToggle = document.getElementById('toggle-left-sidebar');
        if (leftToggle) {
            leftToggle.addEventListener('click', () => this.toggleLeftSidebar());
        }
        
        // Right sidebar toggle
        const rightToggle = document.getElementById('toggle-right-sidebar');
        if (rightToggle) {
            rightToggle.addEventListener('click', () => this.toggleRightSidebar());
        }
        
        // Handle collapsed icon clicks
        this.initializeCollapsedIconHandlers();
    }
    
    /**
     * Initialize collapsed icon handlers
     */
    initializeCollapsedIconHandlers() {
        // Left sidebar collapsed icons
        const leftCollapsedIcons = this.leftSidebar?.querySelectorAll('.collapsed-icon');
        if (leftCollapsedIcons) {
            leftCollapsedIcons.forEach(icon => {
                icon.addEventListener('click', (e) => {
                    e.stopPropagation();
                    
                    // If sidebar is collapsed, expand it and switch to the tab
                    if (this.leftSidebar.classList.contains('collapsed')) {
                        this.toggleLeftSidebar();
                        
                        // Switch to the corresponding tab after expansion
                        setTimeout(() => {
                            const tabName = icon.dataset.tab;
                            const tabButton = document.getElementById(`${tabName}-tab`);
                            if (tabButton) {
                                tabButton.click();
                            }
                        }, 350); // Wait for animation to complete
                    }
                });
            });
        }
        
        // Right sidebar collapsed icons
        const rightCollapsedIcons = this.rightSidebar?.querySelectorAll('.collapsed-icon');
        if (rightCollapsedIcons) {
            rightCollapsedIcons.forEach(icon => {
                icon.addEventListener('click', (e) => {
                    e.stopPropagation();
                    
                    // If sidebar is collapsed, expand it and switch to the tab
                    if (this.rightSidebar.classList.contains('collapsed')) {
                        this.toggleRightSidebar();
                        
                        // Switch to the corresponding tab after expansion
                        setTimeout(() => {
                            const tabName = icon.dataset.tab;
                            const tabButton = document.getElementById(`${tabName}-tab`);
                            if (tabButton) {
                                tabButton.click();
                            }
                        }, 350); // Wait for animation to complete
                    }
                });
            });
        }
    }
    
    /**
     * Toggle left sidebar collapsed state
     */
    toggleLeftSidebar() {
        if (!this.leftSidebar) return;
        
        this.leftSidebar.classList.toggle('collapsed');
        
        // Update button icon
        const toggleBtn = document.getElementById('toggle-left-sidebar');
        if (toggleBtn) {
            const icon = toggleBtn.querySelector('i');
            if (this.leftSidebar.classList.contains('collapsed')) {
                icon.className = 'ri-layout-left-line';
                toggleBtn.title = 'Expand Component Library';
            } else {
                icon.className = 'ri-layout-left-2-line';
                toggleBtn.title = 'Collapse Component Library';
            }
        }
        
        // Trigger canvas resize if GrapesJS is loaded
        setTimeout(() => {
            if (this.editor && typeof this.editor.trigger === 'function') {
                this.editor.trigger('canvas:update-dimensions');
            }
        }, 300);
        
        console.log('🔄 Left sidebar toggled:', this.leftSidebar.classList.contains('collapsed') ? 'collapsed' : 'expanded');
    }
    
    /**
     * Toggle right sidebar collapsed state
     */
    toggleRightSidebar() {
        if (!this.rightSidebar) return;
        
        // Mark as manually toggled
        this.rightSidebarManuallyToggled = true;
        
        this.rightSidebar.classList.toggle('collapsed');
        
        // Update button icon
        const toggleBtn = document.getElementById('toggle-right-sidebar');
        if (toggleBtn) {
            const icon = toggleBtn.querySelector('i');
            if (this.rightSidebar.classList.contains('collapsed')) {
                icon.className = 'ri-layout-right-line';
                toggleBtn.title = 'Show Properties Panel';
            } else {
                icon.className = 'ri-layout-right-2-line';
                toggleBtn.title = 'Hide Properties Panel';
            }
        }
        
        // Trigger canvas resize if GrapesJS is loaded
        this.triggerCanvasResize();
        
        // Reset manual toggle flag after a delay to allow auto-collapse again
        setTimeout(() => {
            this.rightSidebarManuallyToggled = false;
        }, 2000);
        
        console.log('🔄 Right sidebar manually toggled:', this.rightSidebar.classList.contains('collapsed') ? 'collapsed' : 'expanded');
    }
    
    /**
     * Get sidebar states
     */
    getSidebarStates() {
        return {
            leftCollapsed: this.leftSidebar?.classList.contains('collapsed') || false,
            rightCollapsed: this.rightSidebar?.classList.contains('collapsed') || false
        };
    }
    
    /**
     * Set sidebar states
     */
    setSidebarStates(states) {
        if (states.leftCollapsed !== undefined) {
            if (states.leftCollapsed && !this.leftSidebar?.classList.contains('collapsed')) {
                this.toggleLeftSidebar();
            } else if (!states.leftCollapsed && this.leftSidebar?.classList.contains('collapsed')) {
                this.toggleLeftSidebar();
            }
        }
        
        if (states.rightCollapsed !== undefined) {
            if (states.rightCollapsed && !this.rightSidebar?.classList.contains('collapsed')) {
                this.toggleRightSidebar();
            } else if (!states.rightCollapsed && this.rightSidebar?.classList.contains('collapsed')) {
                this.toggleRightSidebar();
            }
        }
    }
    
    /**
     * Initialize editor events for component selection
     */
    initializeEditorEvents() {
        if (!this.editor) return;
        
        // Listen for component selection
        if (typeof this.editor.on === 'function') {
            this.editor.on('component:selected', (component) => {
                this.selectedComponent = component;
                this.handleComponentSelection();
            });
            
            this.editor.on('component:deselected', () => {
                this.selectedComponent = null;
                this.handleComponentDeselection();
            });
        }
    }
    
    /**
     * Initialize auto-collapse behavior
     */
    initializeAutoCollapse() {
        // Auto-collapse right sidebar on page load
        setTimeout(() => {
            if (this.rightSidebar && !this.rightSidebar.classList.contains('collapsed')) {
                this.collapseRightSidebar();
            }
        }, 500);
    }
    
    /**
     * Handle component selection - show right sidebar
     */
    handleComponentSelection() {
        if (this.rightSidebar && this.rightSidebar.classList.contains('collapsed')) {
            this.expandRightSidebar();
        }
        console.log('🎯 Component selected - right sidebar expanded');
    }
    
    /**
     * Handle component deselection - auto-collapse if not manually toggled
     */
    handleComponentDeselection() {
        if (!this.rightSidebarManuallyToggled && this.rightSidebar && !this.rightSidebar.classList.contains('collapsed')) {
            setTimeout(() => {
                // Double check no component is selected after a brief delay
                if (!this.selectedComponent) {
                    this.collapseRightSidebar();
                }
            }, 300);
        }
        console.log('🎯 Component deselected - right sidebar auto-collapsed');
    }
    
    /**
     * Collapse right sidebar
     */
    collapseRightSidebar() {
        if (this.rightSidebar && !this.rightSidebar.classList.contains('collapsed')) {
            this.rightSidebar.classList.add('collapsed');
            this.updateRightSidebarToggleButton();
            this.triggerCanvasResize();
        }
    }
    
    /**
     * Expand right sidebar
     */
    expandRightSidebar() {
        if (this.rightSidebar && this.rightSidebar.classList.contains('collapsed')) {
            this.rightSidebar.classList.remove('collapsed');
            this.updateRightSidebarToggleButton();
            this.triggerCanvasResize();
        }
    }
    
    /**
     * Update right sidebar toggle button state
     */
    updateRightSidebarToggleButton() {
        const toggleBtn = document.getElementById('toggle-right-sidebar');
        if (toggleBtn) {
            const icon = toggleBtn.querySelector('i');
            if (this.rightSidebar?.classList.contains('collapsed')) {
                icon.className = 'ri-layout-right-line';
                toggleBtn.title = 'Show Properties Panel';
            } else {
                icon.className = 'ri-layout-right-2-line';
                toggleBtn.title = 'Hide Properties Panel';
            }
        }
    }
    
    /**
     * Trigger canvas resize
     */
    triggerCanvasResize() {
        setTimeout(() => {
            if (this.editor && typeof this.editor.trigger === 'function') {
                this.editor.trigger('canvas:update-dimensions');
            }
        }, 300);
    }
    
    /**
     * Clean up sidebar manager
     */
    destroy() {
        // Remove event listeners would go here if we stored references
        console.log('🗑️ SidebarManager destroyed');
    }
}

// Export for use in other modules
window.SidebarManager = SidebarManager;
