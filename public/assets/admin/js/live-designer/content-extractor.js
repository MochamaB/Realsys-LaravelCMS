/**
 * ContentExtractor - Extract component data, HTML, styles, and metadata
 * 
 * Features:
 * - Complete component data extraction
 * - HTML content with inline styles
 * - CSS class and style analysis
 * - Metadata and configuration extraction
 * - Export functionality for component templates
 */
class ContentExtractor {
    constructor(selectionManager) {
        this.selectionManager = selectionManager;
        
        // Extraction configurations for different component types
        this.extractionConfigs = {
            page: {
                contentSelector: null, // Extract entire page content
                excludeSelectors: ['.component-toolbar', '.sortable-handle', '.component-selected'],
                includeAttributes: ['data-preview-page', 'data-page-title', 'data-page-template'],
                metadataAttributes: ['data-page-title', 'data-page-template']
            },
            section: {
                contentSelector: null, // Extract entire section content
                excludeSelectors: ['.component-toolbar', '.sortable-handle', '.component-selected'],
                includeAttributes: ['data-section-id', 'data-section-name', 'data-template-section-id', 'data-section-position'],
                metadataAttributes: ['data-section-name', 'data-template-section-id', 'data-section-position']
            },
            widget: {
                contentSelector: null, // Extract entire widget content
                excludeSelectors: ['.component-toolbar', '.sortable-handle', '.component-selected'],
                includeAttributes: ['data-page-section-widget-id', 'data-widget-name', 'data-widget-type', 'data-widget-position', 'data-widget-settings'],
                metadataAttributes: ['data-widget-name', 'data-widget-type', 'data-widget-position', 'data-widget-settings']
            }
        };
        
        console.log('📤 Content extractor initialized');
    }
    
    /**
     * Extract complete component data
     * @param {Object} component - Component to extract
     * @returns {Object} Extracted component data
     */
    extractComponent(component) {
        if (!component || !component.element) {
            console.warn('⚠️ Cannot extract: invalid component');
            return null;
        }
        
        const extractedData = {
            // Basic component info
            type: component.type,
            id: component.id,
            name: component.name,
            metadata: component.metadata,
            
            // Extracted content
            html: this.extractHTML(component),
            css: this.extractCSS(component),
            styles: this.extractInlineStyles(component),
            classes: this.extractClasses(component),
            attributes: this.extractAttributes(component),
            
            // Structure info
            dimensions: this.extractDimensions(component),
            position: this.extractPosition(component),
            children: this.extractChildren(component),
            parent: this.extractParent(component),
            
            // Export info
            exportTimestamp: Date.now(),
            exportUrl: window.location.href
        };
        
        console.log(`📤 Extracted ${component.type} ${component.id}:`, extractedData);
        
        // Notify communicator
        this.selectionManager.communicator.notifyContentExtracted(extractedData);
        
        return extractedData;
    }
    
    /**
     * Extract HTML content from component
     * @param {Object} component - Component to extract HTML from
     * @returns {Object} HTML extraction data
     */
    extractHTML(component) {
        const config = this.extractionConfigs[component.type];
        const element = component.element;
        
        // Clone element to avoid modifying original
        const clone = element.cloneNode(true);
        
        // Remove excluded elements
        config.excludeSelectors.forEach(selector => {
            clone.querySelectorAll(selector).forEach(el => el.remove());
        });
        
        // Clean up selection and sortable classes
        this.cleanupClone(clone);
        
        return {
            outerHTML: clone.outerHTML,
            innerHTML: clone.innerHTML,
            textContent: clone.textContent.trim(),
            tagName: element.tagName.toLowerCase(),
            elementCount: clone.querySelectorAll('*').length,
            textLength: clone.textContent.length
        };
    }
    
    /**
     * Extract CSS styles from component
     * @param {Object} component - Component to extract CSS from
     * @returns {Object} CSS extraction data
     */
    extractCSS(component) {
        const element = component.element;
        const computedStyle = window.getComputedStyle(element);
        
        // Extract important CSS properties
        const importantProperties = [
            'display', 'position', 'width', 'height', 'margin', 'padding',
            'background', 'color', 'font-family', 'font-size', 'font-weight',
            'border', 'border-radius', 'box-shadow', 'opacity', 'z-index',
            'flex', 'grid', 'transform', 'transition'
        ];
        
        const extractedCSS = {};
        const inlineStyles = {};
        
        // Extract computed styles
        importantProperties.forEach(property => {
            const value = computedStyle.getPropertyValue(property);
            if (value && value !== 'initial' && value !== 'auto' && value !== 'normal') {
                extractedCSS[property] = value;
            }
        });
        
        // Extract inline styles
        if (element.style.cssText) {
            const styleDeclarations = element.style.cssText.split(';');
            styleDeclarations.forEach(declaration => {
                const [property, value] = declaration.split(':').map(s => s.trim());
                if (property && value) {
                    inlineStyles[property] = value;
                }
            });
        }
        
        return {
            computed: extractedCSS,
            inline: inlineStyles,
            cssText: element.style.cssText,
            hasInlineStyles: Object.keys(inlineStyles).length > 0
        };
    }
    
    /**
     * Extract inline styles as CSS string
     * @param {Object} component - Component to extract styles from
     * @returns {string} CSS string
     */
    extractInlineStyles(component) {
        const cssData = this.extractCSS(component);
        
        if (!cssData.hasInlineStyles) {
            return '';
        }
        
        return Object.entries(cssData.inline)
            .map(([property, value]) => `${property}: ${value}`)
            .join('; ');
    }
    
    /**
     * Extract CSS classes from component
     * @param {Object} component - Component to extract classes from
     * @returns {Object} Classes data
     */
    extractClasses(component) {
        const element = component.element;
        const allClasses = Array.from(element.classList);
        
        // Filter out system classes
        const systemClasses = [
            'component-selected', 'component-hover', 'sortable-item',
            'sortable-hover', 'sortable-dragging', 'sortable-ghost',
            'sortable-chosen', 'sortable-drag'
        ];
        
        const userClasses = allClasses.filter(cls => 
            !systemClasses.some(sysCls => cls.includes(sysCls))
        );
        
        return {
            all: allClasses,
            user: userClasses,
            system: allClasses.filter(cls => !userClasses.includes(cls)),
            count: userClasses.length,
            className: userClasses.join(' ')
        };
    }
    
    /**
     * Extract attributes from component
     * @param {Object} component - Component to extract attributes from
     * @returns {Object} Attributes data
     */
    extractAttributes(component) {
        const element = component.element;
        const config = this.extractionConfigs[component.type];
        
        const allAttributes = {};
        const dataAttributes = {};
        const metadataAttributes = {};
        
        // Extract all attributes
        Array.from(element.attributes).forEach(attr => {
            allAttributes[attr.name] = attr.value;
            
            // Separate data attributes
            if (attr.name.startsWith('data-')) {
                dataAttributes[attr.name] = attr.value;
            }
            
            // Extract metadata attributes
            if (config.metadataAttributes.includes(attr.name)) {
                metadataAttributes[attr.name] = attr.value;
            }
        });
        
        return {
            all: allAttributes,
            data: dataAttributes,
            metadata: metadataAttributes,
            count: Object.keys(allAttributes).length
        };
    }
    
    /**
     * Extract dimensions from component
     * @param {Object} component - Component to extract dimensions from
     * @returns {Object} Dimensions data
     */
    extractDimensions(component) {
        const element = component.element;
        const rect = element.getBoundingClientRect();
        
        return {
            width: rect.width,
            height: rect.height,
            offsetWidth: element.offsetWidth,
            offsetHeight: element.offsetHeight,
            scrollWidth: element.scrollWidth,
            scrollHeight: element.scrollHeight,
            clientWidth: element.clientWidth,
            clientHeight: element.clientHeight,
            aspectRatio: rect.width / rect.height
        };
    }
    
    /**
     * Extract position from component
     * @param {Object} component - Component to extract position from
     * @returns {Object} Position data
     */
    extractPosition(component) {
        const element = component.element;
        const rect = element.getBoundingClientRect();
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;
        
        return {
            viewport: {
                top: rect.top,
                left: rect.left,
                bottom: rect.bottom,
                right: rect.right
            },
            document: {
                top: rect.top + scrollTop,
                left: rect.left + scrollLeft,
                bottom: rect.bottom + scrollTop,
                right: rect.right + scrollLeft
            },
            offset: {
                top: element.offsetTop,
                left: element.offsetLeft
            }
        };
    }
    
    /**
     * Extract child components
     * @param {Object} component - Parent component
     * @returns {Array} Array of child component data
     */
    extractChildren(component) {
        const children = [];
        
        // Get child component types based on parent type
        const childTypes = {
            page: ['section'],
            section: ['widget'],
            widget: [] // Widgets typically don't have component children
        };
        
        const allowedChildTypes = childTypes[component.type] || [];
        
        allowedChildTypes.forEach(childType => {
            const childComponents = this.selectionManager.detector.getChildComponents(component, childType);
            
            childComponents.forEach(child => {
                children.push({
                    type: child.type,
                    id: child.id,
                    name: child.name,
                    metadata: child.metadata,
                    position: this.extractPosition(child),
                    dimensions: this.extractDimensions(child)
                });
            });
        });
        
        return children;
    }
    
    /**
     * Extract parent component
     * @param {Object} component - Child component
     * @returns {Object|null} Parent component data or null
     */
    extractParent(component) {
        const parent = this.selectionManager.detector.getParentComponent(component);
        
        if (!parent) {
            return null;
        }
        
        return {
            type: parent.type,
            id: parent.id,
            name: parent.name,
            metadata: parent.metadata
        };
    }
    
    /**
     * Clean up cloned element by removing system classes and attributes
     * @param {Element} clone - Cloned element to clean
     */
    cleanupClone(clone) {
        // Remove system classes
        const systemClasses = [
            'component-selected', 'component-hover', 'sortable-item',
            'sortable-hover', 'sortable-dragging', 'sortable-ghost',
            'sortable-chosen', 'sortable-drag'
        ];
        
        // Clean root element
        systemClasses.forEach(cls => {
            clone.classList.remove(cls);
        });
        
        // Clean all descendant elements
        clone.querySelectorAll('*').forEach(el => {
            systemClasses.forEach(cls => {
                el.classList.remove(cls);
            });
            
            // Remove empty class attributes
            if (el.className === '') {
                el.removeAttribute('class');
            }
            
            // Remove system styles
            el.style.removeProperty('outline');
            el.style.removeProperty('outline-offset');
        });
    }
    
    /**
     * Extract component as template
     * @param {Object} component - Component to extract as template
     * @returns {Object} Template data
     */
    extractAsTemplate(component) {
        const extractedData = this.extractComponent(component);
        
        if (!extractedData) {
            return null;
        }
        
        // Create template-specific data
        const template = {
            name: `${component.name} Template`,
            type: component.type,
            description: `Template created from ${component.type} ${component.id}`,
            
            // Template structure
            html: extractedData.html.outerHTML,
            css: this.generateTemplateCSS(extractedData),
            metadata: {
                originalId: component.id,
                originalName: component.name,
                createdAt: Date.now(),
                createdFrom: window.location.href
            },
            
            // Template configuration
            config: {
                editable: true,
                sortable: component.type !== 'page',
                deletable: component.type !== 'page',
                duplicatable: true
            }
        };
        
        console.log(`📋 Created template from ${component.type} ${component.id}:`, template);
        
        return template;
    }
    
    /**
     * Generate CSS for template
     * @param {Object} extractedData - Extracted component data
     * @returns {string} CSS string for template
     */
    generateTemplateCSS(extractedData) {
        const css = [];
        
        // Add inline styles if any
        if (extractedData.styles) {
            css.push(`/* Inline Styles */`);
            css.push(`.template-component { ${extractedData.styles} }`);
        }
        
        // Add computed styles for important properties
        if (extractedData.css.computed) {
            css.push(`/* Computed Styles */`);
            const computedCSS = Object.entries(extractedData.css.computed)
                .map(([property, value]) => `  ${property}: ${value};`)
                .join('\n');
            css.push(`.template-component {\n${computedCSS}\n}`);
        }
        
        return css.join('\n');
    }
    
    /**
     * Export component data as JSON
     * @param {Object} component - Component to export
     * @returns {string} JSON string
     */
    exportAsJSON(component) {
        const extractedData = this.extractComponent(component);
        
        if (!extractedData) {
            return null;
        }
        
        return JSON.stringify(extractedData, null, 2);
    }
    
    /**
     * Export component as HTML file
     * @param {Object} component - Component to export
     * @returns {string} HTML file content
     */
    exportAsHTML(component) {
        const extractedData = this.extractComponent(component);
        
        if (!extractedData) {
            return null;
        }
        
        const html = `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>${extractedData.name} - Exported Component</title>
    <style>
        /* Exported Component Styles */
        ${this.generateTemplateCSS(extractedData)}
        
        /* Reset and base styles */
        * { box-sizing: border-box; }
        body { margin: 0; padding: 20px; font-family: system-ui, -apple-system, sans-serif; }
        .component-container { max-width: 1200px; margin: 0 auto; }
    </style>
</head>
<body>
    <div class="component-container">
        <h1>Exported ${extractedData.type}: ${extractedData.name}</h1>
        <p>Exported on: ${new Date(extractedData.exportTimestamp).toLocaleString()}</p>
        <p>Original URL: <a href="${extractedData.exportUrl}">${extractedData.exportUrl}</a></p>
        
        <div class="exported-component">
            ${extractedData.html.outerHTML}
        </div>
    </div>
</body>
</html>`;
        
        return html;
    }
    
    /**
     * Download component data as file
     * @param {Object} component - Component to download
     * @param {string} format - File format ('json', 'html', 'template')
     */
    downloadComponent(component, format = 'json') {
        let content, filename, mimeType;
        
        switch(format) {
            case 'json':
                content = this.exportAsJSON(component);
                filename = `${component.type}-${component.id}.json`;
                mimeType = 'application/json';
                break;
                
            case 'html':
                content = this.exportAsHTML(component);
                filename = `${component.type}-${component.id}.html`;
                mimeType = 'text/html';
                break;
                
            case 'template':
                content = JSON.stringify(this.extractAsTemplate(component), null, 2);
                filename = `${component.type}-${component.id}-template.json`;
                mimeType = 'application/json';
                break;
                
            default:
                console.warn(`⚠️ Unknown export format: ${format}`);
                return;
        }
        
        if (!content) {
            console.error('❌ Failed to generate content for download');
            return;
        }
        
        // Create and trigger download
        const blob = new Blob([content], { type: mimeType });
        const url = URL.createObjectURL(blob);
        
        const link = document.createElement('a');
        link.href = url;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        URL.revokeObjectURL(url);
        
        console.log(`📥 Downloaded ${component.type} ${component.id} as ${format}`);
    }
    
    /**
     * Get extraction statistics
     * @returns {Object} Extraction statistics
     */
    getStats() {
        const allComponents = [
            ...this.selectionManager.detector.getAllComponentsOfType('page'),
            ...this.selectionManager.detector.getAllComponentsOfType('section'),
            ...this.selectionManager.detector.getAllComponentsOfType('widget')
        ];
        
        return {
            totalComponents: allComponents.length,
            componentTypes: {
                pages: this.selectionManager.detector.getAllComponentsOfType('page').length,
                sections: this.selectionManager.detector.getAllComponentsOfType('section').length,
                widgets: this.selectionManager.detector.getAllComponentsOfType('widget').length
            },
            extractable: allComponents.length > 0
        };
    }
    
    /**
     * Cleanup method for destroying the content extractor
     */
    destroy() {
        console.log('🧹 Content extractor destroyed');
    }
}

// Export for use in other modules
window.ContentExtractor = ContentExtractor;
