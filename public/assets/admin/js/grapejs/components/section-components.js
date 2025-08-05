// public/assets/admin/js/grapejs/components/section-components.js

function registerSectionComponents(editor) {
    const domComponents = editor.DomComponents;
    
    // Base Section Component
    domComponents.addType('cms-section', {
        model: {
            defaults: {
                tagName: 'section',
                classes: ['cms-section'],
                attributes: {
                    'data-section-type': 'default'
                },
                traits: [
                    {
                        type: 'text',
                        name: 'data-section-id',
                        label: 'Section ID'
                    },
                    {
                        type: 'select',
                        name: 'data-section-type',
                        label: 'Section Type',
                        options: [
                            { value: 'full-width', name: 'Full Width' },
                            { value: 'multi-column', name: 'Multi Column' },
                            { value: 'sidebar-left', name: 'Sidebar Left' },
                            { value: 'sidebar-right', name: 'Sidebar Right' }
                        ]
                    },
                    {
                        type: 'text',
                        name: 'css_classes',
                        label: 'CSS Classes'
                    },
                    {
                        type: 'color',
                        name: 'background_color',
                        label: 'Background Color'
                    }
                ],
                // GridStack position data
                grid_x: 0,
                grid_y: 0,
                grid_w: 12,
                grid_h: 4
            },
            
            init() {
                this.on('change:attributes change:classes', this.handleStyleChange);
                this.setupGridStackIntegration();
            },
            
            handleStyleChange() {
                const sectionId = this.get('attributes')['data-section-id'];
                if (sectionId) {
                    this.updateDatabaseStyling(sectionId);
                }
            },
            
            setupGridStackIntegration() {
                // Sync with GridStack positions
                this.on('change:grid_x change:grid_y change:grid_w change:grid_h', () => {
                    this.syncWithGridStack();
                });
            },
            
            updateDatabaseStyling(sectionId) {
                const stylingData = {
                    css_classes: this.getClasses().join(' '),
                    background_color: this.getStyle()['background-color'],
                    grid_x: this.get('grid_x'),
                    grid_y: this.get('grid_y'),
                    grid_w: this.get('grid_w'),
                    grid_h: this.get('grid_h')
                };
                
                // API call to update section styling
                fetch(`/admin/api/sections/${sectionId}/styling`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.csrfToken
                    },
                    body: JSON.stringify(stylingData)
                }).catch(error => {
                    console.error('Failed to update section styling:', error);
                });
            }
        },
        
        view: {
            onRender() {
                this.el.classList.add('gjs-section-component');
                this.setupDropZone();
            },
            
            setupDropZone() {
                // Allow widgets to be dropped into sections
                this.el.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    this.el.classList.add('drop-zone-active');
                });
                
                this.el.addEventListener('dragleave', () => {
                    this.el.classList.remove('drop-zone-active');
                });
            }
        }
    });
    
    // Full Width Section Component
    domComponents.addType('full-width-section', {
        extend: 'cms-section',
        model: {
            defaults: {
                attributes: {
                    'data-section-type': 'full-width'
                },
                classes: ['cms-section', 'full-width-section']
            }
        }
    });
    
    // Multi Column Section Component
    domComponents.addType('multi-column-section', {
        extend: 'cms-section',
        model: {
            defaults: {
                attributes: {
                    'data-section-type': 'multi-column'
                },
                classes: ['cms-section', 'multi-column-section'],
                components: [
                    {
                        tagName: 'div',
                        classes: ['container-fluid'],
                        components: [
                            {
                                tagName: 'div',
                                classes: ['row'],
                                components: [
                                    {
                                        tagName: 'div',
                                        classes: ['col-md-6'],
                                        attributes: { 'data-column': '1' }
                                    },
                                    {
                                        tagName: 'div',
                                        classes: ['col-md-6'],
                                        attributes: { 'data-column': '2' }
                                    }
                                ]
                            }
                        ]
                    }
                ]
            }
        }
    });
}