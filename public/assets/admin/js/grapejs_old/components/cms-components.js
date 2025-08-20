/**
 * Custom CMS Components for GrapesJS
 * Maps CMS sections and widgets to GrapeJS components
 */

/**
 * Register CMS Section Component
 */
function registerSectionComponent(editor) {
    editor.DomComponents.addType('section', {
        model: {
            defaults: {
                tagName: 'div',
                draggable: true,
                droppable: true,
                editable: false,
                selectable: true,
                hoverable: true,
                attributes: {
                    class: 'section-wrapper'
                },
                traits: [
                    {
                        type: 'text',
                        name: 'id',
                        label: 'Section ID',
                        changeProp: true
                    },
                    {
                        type: 'select',
                        name: 'class',
                        label: 'Section Type',
                        options: [
                            { value: 'section-hero', name: 'Hero Section' },
                            { value: 'section-content', name: 'Content Section' },
                            { value: 'section-features', name: 'Features Section' },
                            { value: 'section-testimonials', name: 'Testimonials Section' },
                            { value: 'section-footer', name: 'Footer Section' }
                        ],
                        changeProp: true
                    }
                ]
            },
            
            init() {
                this.on('change:attributes', this.handleAttributeChange);
                this.on('change:traits', this.handleTraitChange);
            },
            
            handleAttributeChange() {
                console.log('Section attributes changed:', this.getAttributes());
            },
            
            handleTraitChange() {
                console.log('Section traits changed:', this.get('traits'));
            }
        },
        
        view: {
            tagName: 'div',
            
            onRender() {
                const sectionId = this.model.get('attributes')['data-section-id'];
                if (sectionId) {
                    this.el.setAttribute('data-section-id', sectionId);
                }
                
                // Add visual indicators for sections in editor
                this.el.style.border = '2px dashed #007bff';
                this.el.style.minHeight = '100px';
                this.el.style.position = 'relative';
                
                // Add section label
                const label = document.createElement('div');
                label.innerHTML = 'Section';
                label.style.cssText = `
                    position: absolute;
                    top: -2px;
                    left: -2px;
                    background: #007bff;
                    color: white;
                    padding: 2px 8px;
                    font-size: 12px;
                    font-weight: bold;
                    pointer-events: none;
                `;
                this.el.appendChild(label);
            }
        }
    });
}

/**
 * Register CMS Widget Component
 */
function registerWidgetComponent(editor) {
    editor.DomComponents.addType('widget', {
        model: {
            defaults: {
                tagName: 'div',
                draggable: true,
                droppable: false,
                editable: false,
                selectable: true,
                hoverable: true,
                attributes: {
                    class: 'widget-container'
                },
                traits: [
                    {
                        type: 'text',
                        name: 'data-widget-id',
                        label: 'Widget ID',
                        changeProp: true
                    },
                    {
                        type: 'text',
                        name: 'data-widget-type',
                        label: 'Widget Type',
                        changeProp: true
                    }
                ]
            },
            
            init() {
                this.on('change:attributes', this.handleAttributeChange);
                this.on('change:traits', this.handleTraitChange);
                
                // Make widget content editable based on type
                this.setupEditableContent();
            },
            
            handleAttributeChange() {
                const widgetId = this.get('attributes')['data-widget-id'];
                const widgetType = this.get('attributes')['data-widget-type'];
                console.log('Widget changed:', { widgetId, widgetType });
            },
            
            handleTraitChange() {
                console.log('Widget traits changed:', this.get('traits'));
            },
            
            setupEditableContent() {
                const widgetType = this.get('attributes')['data-widget-type'];
                
                // Add widget-specific traits
                const traits = this.get('traits') || [];
                
                switch (widgetType) {
                    case 'text-widget':
                        traits.push({
                            type: 'textarea',
                            name: 'content',
                            label: 'Content',
                            changeProp: true
                        });
                        break;
                        
                    case 'image-widget':
                        traits.push({
                            type: 'text',
                            name: 'image-url',
                            label: 'Image URL',
                            changeProp: true
                        }, {
                            type: 'text',
                            name: 'alt-text',
                            label: 'Alt Text',
                            changeProp: true
                        });
                        break;
                        
                    case 'button-widget':
                        traits.push({
                            type: 'text',
                            name: 'button-text',
                            label: 'Button Text',
                            changeProp: true
                        }, {
                            type: 'text',
                            name: 'button-url',
                            label: 'Button URL',
                            changeProp: true
                        });
                        break;
                }
                
                this.set('traits', traits);
            }
        },
        
        view: {
            tagName: 'div',
            
            onRender() {
                const widgetId = this.model.get('attributes')['data-widget-id'];
                const widgetType = this.model.get('attributes')['data-widget-type'];
                
                // Add visual indicators for widgets in editor
                this.el.style.border = '1px solid #28a745';
                this.el.style.margin = '10px';
                this.el.style.padding = '10px';
                this.el.style.minHeight = '60px';
                this.el.style.position = 'relative';
                
                // Add widget label
                const label = document.createElement('div');
                label.innerHTML = `Widget: ${widgetType}`;
                label.style.cssText = `
                    position: absolute;
                    top: -1px;
                    right: -1px;
                    background: #28a745;
                    color: white;
                    padding: 2px 6px;
                    font-size: 10px;
                    font-weight: bold;
                    pointer-events: none;
                `;
                this.el.appendChild(label);
                
                // Enable inline editing for text widgets
                if (widgetType === 'text-widget') {
                    this.el.addEventListener('dblclick', () => {
                        this.enableInlineEditing();
                    });
                }
            },
            
            enableInlineEditing() {
                const contentEl = this.el.querySelector('.widget-content');
                if (contentEl) {
                    contentEl.contentEditable = true;
                    contentEl.focus();
                    
                    contentEl.addEventListener('blur', () => {
                        contentEl.contentEditable = false;
                        this.model.trigger('change:content', contentEl.innerHTML);
                    });
                }
            }
        }
    });
}

/**
 * Initialize all CMS components
 */
function initializeCMSComponents(editor) {
    console.log('🔧 Registering CMS components...');
    
    registerSectionComponent(editor);
    registerWidgetComponent(editor);
    
    console.log('✅ CMS components registered');
}

// Export for use in main GrapeJS file
window.CMSComponents = {
    init: initializeCMSComponents
};