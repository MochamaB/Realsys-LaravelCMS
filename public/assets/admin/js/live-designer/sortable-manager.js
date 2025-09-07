/**
 * SortableManager - Simple sortable functionality for live designer
 * Receives component information and manages drag/drop operations
 */
class SortableManager {
    constructor() {
        this.isEnabled = false;
        this.currentComponent = null;
        
        console.log('📋 Simple SortableManager initialized');
    }
    
    /**
     * Receive component selection and log details
     * @param {Object} component - Selected component object
     */
    onComponentSelected(component) {
        console.log('🔍 SORTABLE: Component selected:', component);
        console.log('🔍 SORTABLE: Component type:', component.type);
        console.log('🔍 SORTABLE: Component ID:', component.id);
        console.log('🔍 SORTABLE: Component element:', component.element);
        
        // Check if we have access to the structured data from LivePreviewController
        if (window.previewPageStructure) {
            console.log('🔍 SORTABLE: Preview page structure available:', window.previewPageStructure);
            this.logStructuredData(component);
        } else {
            console.log('🔍 SORTABLE: No preview page structure found');
        }
        
        if (component.element) {
            console.log('🔍 SORTABLE: Element tag:', component.element.tagName);
            console.log('🔍 SORTABLE: Element classes:', component.element.className);
            console.log('🔍 SORTABLE: Element parent:', component.element.parentElement);
            
            if (component.element.parentElement) {
                console.log('🔍 SORTABLE: Parent tag:', component.element.parentElement.tagName);
                console.log('🔍 SORTABLE: Parent classes:', component.element.parentElement.className);
                console.log('🔍 SORTABLE: Parent children count:', component.element.parentElement.children.length);
            }
        }
        
        this.currentComponent = component;
    }
    
    /**
     * Log structured data from LivePreviewController
     * @param {Object} component - Selected component
     */
    logStructuredData(component) {
        const structure = window.previewPageStructure;
        
        if (component.type === 'page') {
            console.log('🔍 SORTABLE: Page data:', structure.page);
            console.log('🔍 SORTABLE: Available sections:', structure.sections.length);
            structure.sections.forEach((section, index) => {
                console.log(`🔍 SORTABLE: Section ${index + 1}:`, section);
            });
        } else if (component.type === 'section') {
            const sectionData = structure.sections.find(s => s.id == component.id);
            if (sectionData) {
                console.log('🔍 SORTABLE: Section data:', sectionData);
                console.log('🔍 SORTABLE: Available widgets:', sectionData.widgets.length);
                sectionData.widgets.forEach((widget, index) => {
                    console.log(`🔍 SORTABLE: Widget ${index + 1}:`, widget);
                });
            } else {
                console.log('🔍 SORTABLE: Section data not found for ID:', component.id);
            }
        } else if (component.type === 'widget') {
            // Find widget in structure
            let widgetData = null;
            for (const section of structure.sections) {
                widgetData = section.widgets.find(w => w.id == component.id);
                if (widgetData) break;
            }
            
            if (widgetData) {
                console.log('🔍 SORTABLE: Widget data:', widgetData);
            } else {
                console.log('🔍 SORTABLE: Widget data not found for ID:', component.id);
            }
        }
    }
    
    /**
     * Enable sortable functionality for component
     * Called when drag button is clicked
     * @param {Object} component - Component to enable sorting for
     */
    enableSortableForComponent(component) {
        console.log('🔍 SORTABLE: Enabling sortable for component:', component);
        
        // Determine what should be sortable based on component type
        if (component.type === 'page') {
            console.log('🔍 SORTABLE: Page selected - should make sections sortable');
            this.makeSectionsSortable(component);
        } else if (component.type === 'section') {
            console.log('🔍 SORTABLE: Section selected - should make widgets sortable');
            this.makeWidgetsSortable(component);
        } else {
            console.log('🔍 SORTABLE: Widget selected - no children to sort');
            return false;
        }
        
        return true;
    }
    
    /**
     * Make sections sortable within a page
     * @param {Object} pageComponent - Page component
     */
    makeSectionsSortable(pageComponent) {
        console.log('🔍 SORTABLE: Making sections sortable in page:', pageComponent.id);
        
        // Get structured data for this page
        const structure = window.previewPageStructure;
        if (!structure) {
            console.error('❌ SORTABLE: No page structure data available');
            return false;
        }
        
        console.log('🔍 SORTABLE: Page data from structure:', structure.page);
        console.log('🔍 SORTABLE: Sections to make sortable:', structure.sections);
        
        // Find the page container (should be the page element itself)
        const container = pageComponent.element;
        console.log('🔍 SORTABLE: Page container:', container);
        
        // Find all sections within this page using structured data
        const sections = container.querySelectorAll('[data-section-id]');
        console.log('🔍 SORTABLE: Found DOM sections:', sections.length, sections);
        
        // Cross-reference DOM elements with structured data
        structure.sections.forEach((sectionData, index) => {
            const domElement = container.querySelector(`[data-section-id="${sectionData.id}"]`);
            console.log(`🔍 SORTABLE: Section ${index + 1} (ID: ${sectionData.id}):`);
            console.log(`  - Data:`, sectionData);
            console.log(`  - DOM Element:`, domElement);
            
            if (domElement) {
                console.log(`  - Element classes:`, domElement.className);
                console.log(`  - Element position in DOM:`, Array.from(container.children).indexOf(domElement));
            } else {
                console.log(`  - ❌ DOM element not found for section ${sectionData.id}`);
            }
        });
        
        return true;
    }
    
    /**
     * Make widgets sortable within a section
     * @param {Object} sectionComponent - Section component
     */
    makeWidgetsSortable(sectionComponent) {
        console.log('🔍 SORTABLE: Making widgets sortable in section:', sectionComponent.id);
        
        // Get structured data for this section
        const structure = window.previewPageStructure;
        if (!structure) {
            console.error('❌ SORTABLE: No page structure data available');
            return false;
        }
        
        const sectionData = structure.sections.find(s => s.id == sectionComponent.id);
        if (!sectionData) {
            console.error('❌ SORTABLE: Section data not found for ID:', sectionComponent.id);
            return false;
        }
        
        console.log('🔍 SORTABLE: Section data from structure:', sectionData);
        console.log('🔍 SORTABLE: Widgets to make sortable:', sectionData.widgets);
        
        // Find the section container
        const container = sectionComponent.element;
        console.log('🔍 SORTABLE: Section container:', container);
        
        // Find all widgets within this section using structured data
        const widgets = container.querySelectorAll('[data-page-section-widget-id]');
        console.log('🔍 SORTABLE: Found DOM widgets:', widgets.length, widgets);
        
        // Cross-reference DOM elements with structured data
        sectionData.widgets.forEach((widgetData, index) => {
            const domElement = container.querySelector(`[data-page-section-widget-id="${widgetData.id}"]`);
            console.log(`🔍 SORTABLE: Widget ${index + 1} (ID: ${widgetData.id}):`);
            console.log(`  - Data:`, widgetData);
            console.log(`  - DOM Element:`, domElement);
            
            if (domElement) {
                console.log(`  - Element classes:`, domElement.className);
                console.log(`  - Element position in DOM:`, Array.from(container.children).indexOf(domElement));
                console.log(`  - Data position:`, widgetData.position);
            } else {
                console.log(`  - ❌ DOM element not found for widget ${widgetData.id}`);
            }
        });
        
        return true;
    }
    
    /**
     * Disable sortable functionality
     */
    disable() {
        console.log('🔍 SORTABLE: Disabling sortable');
        this.isEnabled = false;
        this.currentComponent = null;
    }
    
    /**
     * Cleanup method
     */
    destroy() {
        this.disable();
        console.log('🧹 SortableManager destroyed');
    }
}

// Make available globally
window.SortableManager = new SortableManager();