// public/assets/admin/js/grapejs/components/widget-components.js

function registerWidgetComponents(editor) {
    const domComponents = editor.DomComponents;
    
    // Base Widget Component
    domComponents.addType('cms-widget', {
        model: {
            defaults: {
                tagName: 'div',
                classes: ['cms-widget'],
                draggable: '.cms-section, [data-column]',
                droppable: false,
                attributes: {
                    'data-widget-type': 'base'
                },
                traits: [
                    {
                        type: 'text',
                        name: 'data-widget-id',
                        label: 'Widget ID'
                    },
                    {
                        type: 'text',
                        name: 'css_classes',
                        label: 'CSS Classes'
                    },
                    {
                        type: 'text',
                        name: 'padding',
                        label: 'Padding'
                    },
                    {
                        type: 'text',
                        name: 'margin',
                        label: 'Margin'
                    }
                ],
                // GridStack position data
                grid_x: 0,
                grid_y: 0,
                grid_w: 6,
                grid_h: 2
            },
            
            init() {
                this.on('change:attributes change:classes', this.handleStyleChange);
                this.loadWidgetData();
            },
            
            loadWidgetData() {
                const widgetId = this.get('attributes')['data-widget-id'];
                if (widgetId) {
                    this.fetchWidgetContent(widgetId);
                }
            },
            
            fetchWidgetContent(widgetId) {
                fetch(`/admin/api/widgets/${widgetId}/content`)
                    .then(response => response.json())
                    .then(data => {
                        this.set('content', data.rendered_content);
                        this.updateTraits(data.fields);
                    })
                    .catch(error => {
                        console.error('Failed to fetch widget content:', error);
                    });
            },
            
            updateTraits(fields) {
                const dynamicTraits = fields.map(field => ({
                    type: this.getTraitType(field.type),
                    name: field.name,
                    label: field.label,
                    value: field.value
                }));
                
                this.set('traits', [...this.get('traits'), ...dynamicTraits]);
            },
            
            getTraitType(fieldType) {
                const typeMap = {
                    'text': 'text',
                    'textarea': 'textarea',
                    'select': 'select',
                    'checkbox': 'checkbox',
                    'color': 'color',
                    'number': 'number'
                };
                return typeMap[fieldType] || 'text';
            },
            
            handleStyleChange() {
                const widgetId = this.get('attributes')['data-widget-id'];
                if (widgetId) {
                    this.updateDatabaseStyling(widgetId);
                }
            },
            
            updateDatabaseStyling(widgetId) {
                const stylingData = {
                    css_classes: this.getClasses().join(' '),
                    padding: this.getTrait('padding')?.get('value'),
                    margin: this.getTrait('margin')?.get('value'),
                    grid_x: this.get('grid_x'),
                    grid_y: this.get('grid_y'),
                    grid_w: this.get('grid_w'),
                    grid_h: this.get('grid_h')
                };
                
                fetch(`/admin/api/widgets/${widgetId}/styling`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.csrfToken
                    },
                    body: JSON.stringify(stylingData)
                }).catch(error => {
                    console.error('Failed to update widget styling:', error);
                });
            }
        },
        
        view: {
            onRender() {
                this.el.classList.add('gjs-widget-component');
                this.setupWidgetToolbar();
            },
            
            setupWidgetToolbar() {
                // Add widget-specific toolbar buttons
                this.el.addEventListener('click', (e) => {
                    if (e.target === this.el) {
                        this.showWidgetEditor();
                    }
                });
            },
            
            showWidgetEditor() {
                // Open widget-specific editing panel
                const widgetId = this.model.get('attributes')['data-widget-id'];
                if (window.GrapesJSWidgetEditor) {
                    window.GrapesJSWidgetEditor.open(widgetId, this.model);
                }
            }
        }
    });
    
    // Text Widget Component
    domComponents.addType('text-widget', {
        extend: 'cms-widget',
        model: {
            defaults: {
                attributes: {
                    'data-widget-type': 'text'
                },
                classes: ['cms-widget', 'text-widget'],
                traits: [
                    {
                        type: 'textarea',
                        name: 'content',
                        label: 'Text Content'
                    },
                    {
                        type: 'select',
                        name: 'text_align',
                        label: 'Text Alignment',
                        options: [
                            { value: 'left', name: 'Left' },
                            { value: 'center', name: 'Center' },
                            { value: 'right', name: 'Right' },
                            { value: 'justify', name: 'Justify' }
                        ]
                    }
                ]
            }
        }
    });
    
    // Image Widget Component
    domComponents.addType('image-widget', {
        extend: 'cms-widget',
        model: {
            defaults: {
                attributes: {
                    'data-widget-type': 'image'
                },
                classes: ['cms-widget', 'image-widget'],
                traits: [
                    {
                        type: 'text',
                        name: 'src',
                        label: 'Image URL'
                    },
                    {
                        type: 'text',
                        name: 'alt',
                        label: 'Alt Text'
                    },
                    {
                        type: 'select',
                        name: 'image_fit',
                        label: 'Image Fit',
                        options: [
                            { value: 'cover', name: 'Cover' },
                            { value: 'contain', name: 'Contain' },
                            { value: 'fill', name: 'Fill' }
                        ]
                    }
                ]
            }
        }
    });
    
    // Button Widget Component
    domComponents.addType('button-widget', {
        extend: 'cms-widget',
        model: {
            defaults: {
                attributes: {
                    'data-widget-type': 'button'
                },
                classes: ['cms-widget', 'button-widget'],
                traits: [
                    {
                        type: 'text',
                        name: 'button_text',
                        label: 'Button Text'
                    },
                    {
                        type: 'text',
                        name: 'button_url',
                        label: 'Button URL'
                    },
                    {
                        type: 'select',
                        name: 'button_style',
                        label: 'Button Style',
                        options: [
                            { value: 'primary', name: 'Primary' },
                            { value: 'secondary', name: 'Secondary' },
                            { value: 'outline', name: 'Outline' }
                        ]
                    }
                ]
            }
        }
    });
}