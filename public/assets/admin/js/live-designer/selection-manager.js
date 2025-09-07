/**
 * SelectionManager - Core orchestrator for the live preview selection system
 * 
 * Features:
 * - Centralized selection state management
 * - Mode switching (select, sort, edit)
 * - Integration with all other managers
 * - Event coordination and lifecycle management
 */
class SelectionManager {
    constructor() {
        this.selectedComponent = null;
        this.currentMode = 'select'; // 'select', 'sort', 'edit'
        this.isInitialized = false;
        
        // Manager instances
        this.communicator = null;
        this.detector = null;
        this.toolbar = null;
        this.sortableManager = null;
        this.contentExtractor = null;
        
        // Event listeners storage
        this.eventListeners = new Map();
        
        // Selection history for undo/redo
        this.selectionHistory = [];
        this.historyIndex = -1;
        
        console.log('🎯 Selection manager created');
    }
    
    /**
     * Initialize the selection manager and all sub-managers
     */
    async initialize() {
        if (this.isInitialized) {
            console.warn('⚠️ Selection manager already initialized');
            return;
        }
        
        try {
            // Initialize iframe communicator first
            this.communicator = new IframeCommunicator();
            
            // Initialize component detector
            this.detector = new ComponentDetector(this);
            
            // Initialize component toolbar
            this.toolbar = new ComponentToolbar(this);
            
            // Initialize sortable manager (will be created when needed)
            // this.sortableManager = new SortableManager(this);
            
            // Initialize content extractor
            this.contentExtractor = new ContentExtractor(this);
            
            // Setup global event listeners
            this.setupGlobalEvents();
            
            // Register keyboard shortcuts
            this.setupKeyboardShortcuts();
            
            // Make available globally
            window.selectionManager = this;
            window.iframeCommunicator = this.communicator;
            window.componentDetector = this.detector;
            window.componentToolbar = this.toolbar;
            window.contentExtractor = this.contentExtractor;
            
            this.isInitialized = true;
            
            console.log('✅ Selection manager initialized successfully');
            
            // Notify parent that selection system is ready
            this.communicator.sendMessage('selection-system:ready', {
                mode: this.currentMode,
                stats: this.detector.getDetectionStats()
            });
            
        } catch (error) {
            console.error('❌ Failed to initialize selection manager:', error);
            throw error;
        }
    }
    
    /**
     * Setup global event listeners
     */
    setupGlobalEvents() {
        // Handle window resize for toolbar repositioning
        this.addEventListener(window, 'resize', () => {
            if (this.toolbar.isToolbarVisible()) {
                this.toolbar.updatePosition();
            }
        });
        
        // Handle scroll for toolbar repositioning
        this.addEventListener(window, 'scroll', () => {
            if (this.toolbar.isToolbarVisible()) {
                this.toolbar.updatePosition();
            }
        });
        
        // Handle escape key to deselect
        this.addEventListener(document, 'keydown', (e) => {
            if (e.key === 'Escape') {
                this.deselect();
            }
        });
        
        // Handle clicks outside components to deselect
        this.addEventListener(document, 'click', (e) => {
            // Only handle if no component was detected
            const component = this.detector.identifyComponent(e.target);
            if (!component && this.selectedComponent) {
                this.deselect();
            }
        }, true);
    }
    
    /**
     * Setup keyboard shortcuts
     */
    setupKeyboardShortcuts() {
        this.addEventListener(document, 'keydown', (e) => {
            // Ignore if user is typing in an input
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.isContentEditable) {
                return;
            }
            
            // Handle keyboard shortcuts
            switch(e.key) {
                case 'Delete':
                case 'Backspace':
                    if (this.selectedComponent && (this.selectedComponent.type === 'section' || this.selectedComponent.type === 'widget')) {
                        e.preventDefault();
                        this.toolbar.handleDeleteAction(this.selectedComponent);
                    }
                    break;
                    
                case 'ArrowUp':
                    if (e.ctrlKey && this.selectedComponent) {
                        e.preventDefault();
                        this.toolbar.handleMoveAction(this.selectedComponent, 'up');
                    }
                    break;
                    
                case 'ArrowDown':
                    if (e.ctrlKey && this.selectedComponent) {
                        e.preventDefault();
                        this.toolbar.handleMoveAction(this.selectedComponent, 'down');
                    }
                    break;
                    
                case 'd':
                    if (e.ctrlKey && this.selectedComponent) {
                        e.preventDefault();
                        this.toolbar.handleDuplicateAction(this.selectedComponent);
                    }
                    break;
                    
                case 'e':
                    if (e.ctrlKey && this.selectedComponent) {
                        e.preventDefault();
                        this.toolbar.handleEditAction(this.selectedComponent);
                    }
                    break;
                    
                case 's':
                    if (e.ctrlKey && this.selectedComponent) {
                        e.preventDefault();
                        this.toolbar.handleSettingsAction(this.selectedComponent);
                    }
                    break;
            }
        });
    }
    
    /**
     * Select a component
     * @param {Object} component - Component to select
     */
    select(component) {
        if (!component) {
            console.warn('⚠️ Cannot select null component');
            return;
        }
        
        // Deselect current component first
        if (this.selectedComponent) {
            this.deselect();
        }
        
        // Set new selection
        this.selectedComponent = component;
        
        // Add selection visual feedback
        this.addSelectionFeedback(component);
        
        // Show toolbar
        this.toolbar.show(component);
        
        // Notify sortable manager about component selection
        if (window.SortableManager && window.SortableManager.onComponentSelected) {
            window.SortableManager.onComponentSelected(component);
        }
        
        // Add to selection history
        this.addToHistory(component);
        
        // Notify communicator
        this.communicator.notifyComponentSelected(component);
        
        console.log(`🎯 Selected ${component.type} ${component.id}: ${component.name}`);
    }
    
    /**
     * Deselect current component
     */
    deselect() {
        if (!this.selectedComponent) {
            return;
        }
        
        const previousComponent = this.selectedComponent;
        
        // Remove selection visual feedback
        this.removeSelectionFeedback(this.selectedComponent);
        
        // Hide toolbar
        this.toolbar.hide();
        
        // Clear selection
        this.selectedComponent = null;
        
        // Notify communicator
        this.communicator.notifyComponentDeselected(previousComponent);
        
        console.log(`🎯 Deselected ${previousComponent.type} ${previousComponent.id}`);
    }
    
    /**
     * Add visual feedback for selected component
     * @param {Object} component - Selected component
     */
    addSelectionFeedback(component) {
        // Remove any existing selection classes
        this.removeAllSelectionFeedback();
        
        // Add selection classes
        component.element.classList.add('component-selected');
        component.element.classList.add(`component-selected--${component.type}`);
        
        // Add selection outline
        component.element.style.outline = '2px solid #007bff';
        component.element.style.outlineOffset = '2px';
    }
    
    /**
     * Remove visual feedback from component
     * @param {Object} component - Component to remove feedback from
     */
    removeSelectionFeedback(component) {
        component.element.classList.remove('component-selected');
        component.element.classList.remove(`component-selected--${component.type}`);
        component.element.style.outline = '';
        component.element.style.outlineOffset = '';
    }
    
    /**
     * Remove all selection feedback from document
     */
    removeAllSelectionFeedback() {
        document.querySelectorAll('.component-selected').forEach(el => {
            el.classList.remove('component-selected');
            el.classList.remove('component-selected--widget');
            el.classList.remove('component-selected--section');
            el.classList.remove('component-selected--page');
            el.style.outline = '';
            el.style.outlineOffset = '';
        });
    }
    
    /**
     * Change selection mode
     * @param {string} mode - New mode ('select', 'sort', 'edit')
     */
    setMode(mode) {
        if (this.currentMode === mode) {
            return;
        }
        
        const previousMode = this.currentMode;
        this.currentMode = mode;
        
        // Handle mode-specific logic
        switch(mode) {
            case 'select':
                this.enableSelectMode();
                break;
            case 'sort':
                this.enableSortMode();
                break;
            case 'edit':
                this.enableEditMode();
                break;
            default:
                console.warn(`⚠️ Unknown mode: ${mode}`);
                this.currentMode = previousMode;
                return;
        }
        
        console.log(`🔄 Mode changed from ${previousMode} to ${mode}`);
        
        // Notify communicator
        this.communicator.sendMessage('mode:changed', {
            previousMode: previousMode,
            currentMode: mode
        });
    }
    
    /**
     * Enable select mode
     */
    enableSelectMode() {
        // Enable component detection
        this.detector.setEnabled(true);
        
        // Disable sort mode if active
        if (this.sortableManager) {
            this.sortableManager.disable();
        }
        
        // Update body class
        document.body.classList.remove('mode-sort', 'mode-edit');
        document.body.classList.add('mode-select');
    }
    
    /**
     * Enable sort mode
     * @param {string} componentType - Type of components to make sortable
     */
    enableSortMode(componentType = 'section') {
        // Disable component detection temporarily
        this.detector.setEnabled(false);
        
        // Deselect current component
        this.deselect();
        
        // Initialize sortable manager if not exists
        if (!this.sortableManager && window.SortableManager) {
            this.sortableManager = new SortableManager(this);
            window.sortableManager = this.sortableManager;
        }
        
        // Enable sortable for component type
        if (this.sortableManager) {
            this.sortableManager.enable(componentType);
        }
        
        // Update body class
        document.body.classList.remove('mode-select', 'mode-edit');
        document.body.classList.add('mode-sort');
        
        console.log(`📋 Sort mode enabled for ${componentType}s`);
    }
    
    /**
     * Disable sort mode
     */
    disableSortMode() {
        if (this.sortableManager) {
            this.sortableManager.disable();
        }
        
        this.setMode('select');
    }
    
    /**
     * Enable edit mode (for future inline editing)
     */
    enableEditMode() {
        // Disable component detection
        this.detector.setEnabled(false);
        
        // Update body class
        document.body.classList.remove('mode-select', 'mode-sort');
        document.body.classList.add('mode-edit');
        
        console.log('✏️ Edit mode enabled');
    }
    
    /**
     * Handle zoom changes
     * @param {Object} zoomData - Zoom level and inverse scale
     */
    handleZoomChange(zoomData) {
        // Update toolbar positioning
        if (this.toolbar) {
            this.toolbar.handleZoomChange(zoomData);
        }
        
        // Update sortable manager if active
        if (this.sortableManager) {
            this.sortableManager.handleZoomChange(zoomData);
        }
    }
    
    /**
     * Add component to selection history
     * @param {Object} component - Component to add to history
     */
    addToHistory(component) {
        // Remove any history after current index
        this.selectionHistory = this.selectionHistory.slice(0, this.historyIndex + 1);
        
        // Add new component
        this.selectionHistory.push({
            type: component.type,
            id: component.id,
            timestamp: Date.now()
        });
        
        this.historyIndex = this.selectionHistory.length - 1;
        
        // Limit history size
        if (this.selectionHistory.length > 50) {
            this.selectionHistory.shift();
            this.historyIndex--;
        }
    }
    
    /**
     * Go back in selection history
     */
    selectPrevious() {
        if (this.historyIndex > 0) {
            this.historyIndex--;
            const historyItem = this.selectionHistory[this.historyIndex];
            const element = this.detector.findComponentById(historyItem.type, historyItem.id);
            
            if (element) {
                const component = this.detector.createComponentObject(historyItem.type, element);
                this.select(component);
            }
        }
    }
    
    /**
     * Go forward in selection history
     */
    selectNext() {
        if (this.historyIndex < this.selectionHistory.length - 1) {
            this.historyIndex++;
            const historyItem = this.selectionHistory[this.historyIndex];
            const element = this.detector.findComponentById(historyItem.type, historyItem.id);
            
            if (element) {
                const component = this.detector.createComponentObject(historyItem.type, element);
                this.select(component);
            }
        }
    }
    
    /**
     * Get current selection state
     * @returns {Object} Selection state
     */
    getState() {
        return {
            selectedComponent: this.selectedComponent ? {
                type: this.selectedComponent.type,
                id: this.selectedComponent.id,
                name: this.selectedComponent.name
            } : null,
            currentMode: this.currentMode,
            isInitialized: this.isInitialized,
            historyLength: this.selectionHistory.length,
            historyIndex: this.historyIndex
        };
    }
    
    /**
     * Add event listener and track it for cleanup
     * @param {Element} element - Element to add listener to
     * @param {string} event - Event name
     * @param {Function} handler - Event handler
     * @param {boolean} capture - Use capture phase
     */
    addEventListener(element, event, handler, capture = false) {
        element.addEventListener(event, handler, capture);
        
        // Track for cleanup
        if (!this.eventListeners.has(element)) {
            this.eventListeners.set(element, []);
        }
        
        this.eventListeners.get(element).push({
            event: event,
            handler: handler,
            capture: capture
        });
    }
    
    /**
     * Remove all tracked event listeners
     */
    removeAllEventListeners() {
        this.eventListeners.forEach((listeners, element) => {
            listeners.forEach(({ event, handler, capture }) => {
                element.removeEventListener(event, handler, capture);
            });
        });
        
        this.eventListeners.clear();
    }
    
    /**
     * Enable sortable functionality for a component's children
     * Called when drag button is clicked from toolbar
     * @param {Object} component - Selected component
     */
    enableSortableForComponent(component) {
        console.log(`🔍 SELECTION: Delegating sortable enable to SortableManager for ${component.type} ${component.id}`);
        
        // Delegate to the sortable manager
        if (window.SortableManager && window.SortableManager.enableSortableForComponent) {
            return window.SortableManager.enableSortableForComponent(component);
        } else {
            console.error('❌ SortableManager not available');
            return false;
        }
    }

    /**
     * Cleanup and destroy the selection manager
     */
    destroy() {
        // Deselect current component
        this.deselect();
        
        // Remove all event listeners
        this.removeAllEventListeners();
        
        // Destroy sub-managers
        if (this.detector) {
            this.detector.destroy();
        }
        
        if (this.toolbar) {
            this.toolbar.destroy();
        }
        
        if (this.sortableManager) {
            this.sortableManager.destroy();
        }
        
        if (this.communicator) {
            this.communicator.destroy();
        }
        
        // Clear global references
        window.selectionManager = null;
        window.iframeCommunicator = null;
        window.componentDetector = null;
        window.componentToolbar = null;
        window.contentExtractor = null;
        window.sortableManager = null;
        
        // Reset state
        this.selectedComponent = null;
        this.currentMode = 'select';
        this.isInitialized = false;
        this.selectionHistory = [];
        this.historyIndex = -1;
        
        console.log('🧹 Selection manager destroyed');
    }
}

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', async () => {
    try {
        const selectionManager = new SelectionManager();
        await selectionManager.initialize();
    } catch (error) {
        console.error('❌ Failed to auto-initialize selection manager:', error);
    }
});

// Export for manual initialization if needed
window.SelectionManager = SelectionManager;
