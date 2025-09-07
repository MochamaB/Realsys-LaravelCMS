/**
 * ComponentDetector - Unified component identification and click handling
 * 
 * Features:
 * - Single click handler for all component types
 * - Priority-based detection (widget → section → page)
 * - DOM tree traversal for component discovery
 * - Component metadata extraction from data attributes
 */
class ComponentDetector {
    constructor(selectionManager) {
        this.selectionManager = selectionManager;
        this.componentSelectors = {
            widget: '[data-page-section-widget-id]',
            section: '[data-section-id]',
            page: '[data-preview-page]'
        };
        
        this.componentAttributes = {
            widget: {
                id: 'data-page-section-widget-id',
                name: 'data-widget-name',
                type: 'data-widget-type',
                position: 'data-widget-position',
                settings: 'data-widget-settings'
            },
            section: {
                id: 'data-section-id',
                name: 'data-section-name',
                templateId: 'data-template-section-id',
                position: 'data-section-position'
            },
            page: {
                id: 'data-preview-page',
                name: 'data-page-title',
                template: 'data-page-template'
            }
        };
        
        this.isEnabled = true;
        this.bindEvents();
        
        console.log('🎯 Component detector initialized');
    }
    
    /**
     * Bind click event handler for component detection
     */
    bindEvents() {
        // Use capture phase to intercept clicks before they bubble
        document.addEventListener('click', (e) => {
            if (!this.isEnabled) return;
            
            e.preventDefault();
            e.stopPropagation();
            
            const component = this.identifyComponent(e.target);
            if (component) {
                this.selectionManager.select(component);
            } else {
                this.selectionManager.deselect();
            }
        }, true);
        
        // Also handle hover for visual feedback
        document.addEventListener('mouseover', (e) => {
            if (!this.isEnabled || this.selectionManager.currentMode !== 'select') return;
            
            const component = this.identifyComponent(e.target);
            if (component && component !== this.selectionManager.selectedComponent) {
                this.addHoverFeedback(component.element, component.type);
            }
        }, true);
        
        document.addEventListener('mouseout', (e) => {
            if (!this.isEnabled) return;
            
            const component = this.identifyComponent(e.target);
            if (component) {
                this.removeHoverFeedback(component.element);
            }
        }, true);
    }
    
    /**
     * Identify component from clicked element by traversing DOM tree
     * @param {Element} element - Clicked element
     * @returns {Object|null} Component object or null
     */
    identifyComponent(element) {
        let current = element;
        
        // Walk up DOM tree to find component
        while (current && current !== document.body) {
            // Check in priority order: widget → section → page
            // This ensures nested components are detected correctly
            
            if (current.hasAttribute('data-page-section-widget-id')) {
                return this.createComponentObject('widget', current);
            }
            
            if (current.hasAttribute('data-section-id')) {
                return this.createComponentObject('section', current);
            }
            
            if (current.hasAttribute('data-preview-page')) {
                return this.createComponentObject('page', current);
            }
            
            current = current.parentElement;
        }
        
        return null;
    }
    
    /**
     * Create standardized component object from DOM element
     * @param {string} type - Component type (widget, section, page)
     * @param {Element} element - DOM element
     * @returns {Object} Component object
     */
    createComponentObject(type, element) {
        const attributes = this.componentAttributes[type];
        
        const componentData = {
            type: type,
            element: element,
            id: this.extractId(type, element),
            name: this.extractName(type, element),
            metadata: this.extractMetadata(type, element)
        };
        
        return componentData;
    }
    
    /**
     * Extract component ID from element
     * @param {string} type - Component type
     * @param {Element} element - DOM element
     * @returns {string} Component ID
     */
    extractId(type, element) {
        const attributes = this.componentAttributes[type];
        return element.getAttribute(attributes.id) || '';
    }
    
    /**
     * Extract component name from element
     * @param {string} type - Component type
     * @param {Element} element - DOM element
     * @returns {string} Component name
     */
    extractName(type, element) {
        const attributes = this.componentAttributes[type];
        const name = element.getAttribute(attributes.name);
        
        if (name) {
            return name;
        }
        
        // Fallback to type + ID
        const id = this.extractId(type, element);
        return `${type.charAt(0).toUpperCase() + type.slice(1)} ${id}`;
    }
    
    /**
     * Extract component metadata from element
     * @param {string} type - Component type
     * @param {Element} element - DOM element
     * @returns {Object} Component metadata
     */
    extractMetadata(type, element) {
        const metadata = {};
        
        switch(type) {
            case 'page':
                metadata.template = element.getAttribute('data-page-template') || 'Unknown';
                metadata.title = element.getAttribute('data-page-title') || '';
                break;
                
            case 'section':
                metadata.templateSectionId = element.getAttribute('data-template-section-id') || '';
                metadata.position = element.getAttribute('data-section-position') || '0';
                metadata.name = element.getAttribute('data-section-name') || '';
                break;
                
            case 'widget':
                metadata.widgetType = element.getAttribute('data-widget-type') || 'unknown';
                metadata.position = element.getAttribute('data-widget-position') || '0';
                metadata.settings = this.parseWidgetSettings(element.getAttribute('data-widget-settings'));
                break;
        }
        
        // Add common metadata
        metadata.dimensions = this.getElementDimensions(element);
        metadata.classes = Array.from(element.classList);
        metadata.tagName = element.tagName.toLowerCase();
        
        return metadata;
    }
    
    /**
     * Parse widget settings JSON
     * @param {string} settingsJson - JSON string of widget settings
     * @returns {Object} Parsed settings object
     */
    parseWidgetSettings(settingsJson) {
        if (!settingsJson) return {};
        
        try {
            return JSON.parse(settingsJson);
        } catch (error) {
            console.warn('⚠️ Failed to parse widget settings:', error);
            return {};
        }
    }
    
    /**
     * Get element dimensions and position
     * @param {Element} element - DOM element
     * @returns {Object} Dimensions object
     */
    getElementDimensions(element) {
        const rect = element.getBoundingClientRect();
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;
        
        return {
            width: rect.width,
            height: rect.height,
            top: rect.top + scrollTop,
            left: rect.left + scrollLeft,
            bottom: rect.bottom + scrollTop,
            right: rect.right + scrollLeft,
            offsetWidth: element.offsetWidth,
            offsetHeight: element.offsetHeight,
            scrollWidth: element.scrollWidth,
            scrollHeight: element.scrollHeight
        };
    }
    
    /**
     * Add hover visual feedback to component
     * @param {Element} element - Component element
     * @param {string} type - Component type
     */
    addHoverFeedback(element, type) {
        // Remove existing hover classes
        this.removeAllHoverFeedback();
        
        // Add type-specific hover class
        element.classList.add('component-hover');
        element.classList.add(`component-hover--${type}`);
    }
    
    /**
     * Remove hover feedback from component
     * @param {Element} element - Component element
     */
    removeHoverFeedback(element) {
        element.classList.remove('component-hover');
        element.classList.remove('component-hover--widget');
        element.classList.remove('component-hover--section');
        element.classList.remove('component-hover--page');
    }
    
    /**
     * Remove all hover feedback from document
     */
    removeAllHoverFeedback() {
        document.querySelectorAll('.component-hover').forEach(el => {
            this.removeHoverFeedback(el);
        });
    }
    
    /**
     * Find component element by type and ID
     * @param {string} type - Component type
     * @param {string} id - Component ID
     * @returns {Element|null} Found element or null
     */
    findComponentById(type, id) {
        const attributes = this.componentAttributes[type];
        if (!attributes) return null;
        
        const selector = `[${attributes.id}="${id}"]`;
        return document.querySelector(selector);
    }
    
    /**
     * Get all components of a specific type
     * @param {string} type - Component type
     * @returns {Array} Array of component objects
     */
    getAllComponentsOfType(type) {
        const selector = this.componentSelectors[type];
        if (!selector) return [];
        
        const elements = document.querySelectorAll(selector);
        return Array.from(elements).map(element => 
            this.createComponentObject(type, element)
        );
    }
    
    /**
     * Get parent component of given component
     * @param {Object} component - Child component
     * @returns {Object|null} Parent component or null
     */
    getParentComponent(component) {
        let parent = component.element.parentElement;
        
        while (parent && parent !== document.body) {
            const parentComponent = this.identifyComponent(parent);
            if (parentComponent && parentComponent.type !== component.type) {
                return parentComponent;
            }
            parent = parent.parentElement;
        }
        
        return null;
    }
    
    /**
     * Get child components of given component
     * @param {Object} component - Parent component
     * @param {string} childType - Type of children to find
     * @returns {Array} Array of child components
     */
    getChildComponents(component, childType) {
        const selector = this.componentSelectors[childType];
        if (!selector) return [];
        
        const childElements = component.element.querySelectorAll(selector);
        return Array.from(childElements).map(element =>
            this.createComponentObject(childType, element)
        );
    }
    
    /**
     * Check if element is within a component of specific type
     * @param {Element} element - Element to check
     * @param {string} componentType - Type to check for
     * @returns {boolean} True if element is within component type
     */
    isWithinComponentType(element, componentType) {
        const component = this.identifyComponent(element);
        return component && component.type === componentType;
    }
    
    /**
     * Enable/disable component detection
     * @param {boolean} enabled - Whether detection should be enabled
     */
    setEnabled(enabled) {
        this.isEnabled = enabled;
        
        if (!enabled) {
            this.removeAllHoverFeedback();
        }
        
        console.log(`🎯 Component detection ${enabled ? 'enabled' : 'disabled'}`);
    }
    
    /**
     * Get detection statistics
     * @returns {Object} Statistics about detected components
     */
    getDetectionStats() {
        const stats = {
            pages: this.getAllComponentsOfType('page').length,
            sections: this.getAllComponentsOfType('section').length,
            widgets: this.getAllComponentsOfType('widget').length
        };
        
        stats.total = stats.pages + stats.sections + stats.widgets;
        
        return stats;
    }
    
    /**
     * Cleanup method for destroying the detector
     */
    destroy() {
        this.setEnabled(false);
        this.removeAllHoverFeedback();
        console.log('🧹 Component detector destroyed');
    }
}

// Export for use in other modules
window.ComponentDetector = ComponentDetector;
