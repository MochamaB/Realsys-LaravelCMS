// page-designer.js
document.addEventListener('DOMContentLoaded', async function () {
    const editor = grapesjs.init({
        container: '#gjs',
        height: '80vh',
        width: 'auto',
        storageManager: false,
        blockManager: { appendTo: '.panel__blocks' }
    });

    // Get page ID from somewhere (URL, data attribute, etc.)
    const pageId = getPageId(); // You'll need to implement this function
    
    // --- Define custom component types BEFORE adding blocks ---
    
    // Define a custom widget component type
    editor.DomComponents.addType('widget', {
        isComponent: el => el.hasAttribute && el.hasAttribute('data-widget-type'),
        model: {
            defaults: {
                tagName: 'div',
                attributes: { 'data-widget-type': 'widget' },
                droppable: false,
                draggable: '[data-section-type]', // Only draggable inside sections
                // Default widget structure
                components: [
                    {
                        tagName: 'div',
                        attributes: { class: 'widget-placeholder' },
                        components: [
                            { type: 'text', content: 'Widget Placeholder' }
                        ]
                    }
                ]
            },
            init() {
                // Initialize widget with data from widgetSlug and widgetId
                const widgetSlug = this.get('widgetSlug');
                const widgetId = this.get('widgetId');
                const widgetName = this.get('name');
                
                if (widgetName) {
                    this.components().reset([
                        {
                            tagName: 'div',
                            attributes: { class: 'widget-placeholder' },
                            components: [
                                { type: 'text', content: widgetName }
                            ]
                        }
                    ]);
                }
            }
        },
    });

    // Make sections droppable for widgets
    editor.DomComponents.addType('section', {
        isComponent: el => el.hasAttribute && el.hasAttribute('data-section-type'),
        model: {
            defaults: {
                droppable: '[data-widget-type]',
                draggable: true,
                resizable: {
                    bc: 1, // Enable bottom center resize
                    minDim: 100,
                    maxDim: 1000,
                },
                traits: [
                    {
                        type: 'text',
                        label: 'Section Name',
                        name: 'data-section-name',
                        changeProp: 1
                    },
                    {
                        type: 'color',
                        label: 'Background',
                        name: 'style:background-color',
                        changeProp: 1
                    },
                    {
                        type: 'text',
                        label: 'Padding',
                        name: 'style:padding',
                        placeholder: 'e.g. 2rem 0',
                        changeProp: 1
                    },
                    {
                        type: 'text',
                        label: 'Margin',
                        name: 'style:margin',
                        placeholder: 'e.g. 0 0 2rem 0',
                        changeProp: 1
                    },
                    {
                        type: 'text',
                        label: 'CSS Classes',
                        name: 'class',
                        changeProp: 1
                    }
                ]
            },
            init() {
                this.on('change:attributes:data-section-name', () => {
                    // Optionally update inner text or other logic
                });
                this.on('change:style:background-color change:style:padding change:style:margin change:class', () => {
                    // Save section edits to backend
                    saveSectionEdit(this);
                });
            }
        }
    });

    // --- Load existing sections from API ---
    if (pageId) {
        try {
            await window.PageManager.loadSectionsToGrapesJS(editor, pageId);
            console.log('Sections loaded successfully');
        } catch (error) {
            console.error('Error loading sections:', error);
        }
    }

    // --- Section Blocks for adding new sections ---
    const sectionBlocks = [
        {
            id: 'section-full-width',
            label: 'Full Width Section',
            category: 'Sections',
            attributes: { class: 'fa fa-arrows-h' },
            content: {
                tagName: 'section',
                attributes: { 
                    'data-section-type': 'full-width', 
                    class: 'section section-full-width', 
                    style: 'min-height:200px;' 
                },
                components: [
                    {
                        tagName: 'div',
                        attributes: { class: 'container' },
                        components: [
                            {
                                tagName: 'div',
                                attributes: { class: 'row' },
                                components: [
                                    {
                                        tagName: 'div',
                                        attributes: { class: 'col-12' },
                                        components: [
                                            { type: 'text', content: 'Full Width Section' }
                                        ]
                                    }
                                ]
                            }
                        ]
                    }
                ]
            }
        },
        {
            id: 'section-multi-column',
            label: 'Multi-Column Section',
            category: 'Sections',
            attributes: { class: 'fa fa-columns' },
            content: {
                tagName: 'section',
                attributes: { 
                    'data-section-type': 'multi-column', 
                    class: 'section section-multi-column', 
                    style: 'min-height:200px;' 
                },
                components: [
                    {
                        tagName: 'div',
                        attributes: { class: 'container' },
                        components: [
                            {
                                tagName: 'div',
                                attributes: { class: 'row' },
                                components: [
                                    {
                                        tagName: 'div',
                                        attributes: { class: 'col-md-6' },
                                        components: [
                                            { type: 'text', content: 'Column 1' }
                                        ]
                                    },
                                    {
                                        tagName: 'div',
                                        attributes: { class: 'col-md-6' },
                                        components: [
                                            { type: 'text', content: 'Column 2' }
                                        ]
                                    }
                                ]
                            }
                        ]
                    }
                ]
            }
        },
        {
            id: 'section-sidebar-left',
            label: 'Sidebar Left Section',
            category: 'Sections',
            attributes: { class: 'fa fa-align-left' },
            content: {
                tagName: 'section',
                attributes: { 
                    'data-section-type': 'sidebar-left', 
                    class: 'section section-sidebar-left', 
                    style: 'min-height:200px;' 
                },
                components: [
                    {
                        tagName: 'div',
                        attributes: { class: 'container' },
                        components: [
                            {
                                tagName: 'div',
                                attributes: { class: 'row' },
                                components: [
                                    {
                                        tagName: 'div',
                                        attributes: { class: 'col-md-3' },
                                        components: [
                                            { type: 'text', content: 'Sidebar' }
                                        ]
                                    },
                                    {
                                        tagName: 'div',
                                        attributes: { class: 'col-md-9' },
                                        components: [
                                            { type: 'text', content: 'Main Content' }
                                        ]
                                    }
                                ]
                            }
                        ]
                    }
                ]
            }
        },
        {
            id: 'section-default',
            label: 'Default Section',
            category: 'Sections',
            attributes: { class: 'fa fa-square-o' },
            content: {
                tagName: 'section',
                attributes: { 
                    'data-section-type': 'default', 
                    class: 'section section-default', 
                    style: 'min-height:200px;' 
                },
                components: [
                    {
                        tagName: 'div',
                        attributes: { class: 'container' },
                        components: [
                            {
                                tagName: 'div',
                                attributes: { class: 'row' },
                                components: [
                                    {
                                        tagName: 'div',
                                        attributes: { class: 'col-12' },
                                        components: [
                                            { type: 'text', content: 'Default Section' }
                                        ]
                                    }
                                ]
                            }
                        ]
                    }
                ]
            }
        }
    ];
    
    sectionBlocks.forEach(block => editor.BlockManager.add(block.id, block));

    // --- Widget Blocks ---
    // Fetch widgets from the API
    try {
        const res = await fetch('/admin/api/widgets', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': window.csrfToken
            }
        });
        const data = await res.json();
        (data.widgets || []).forEach(widget => {
            const labelHtml = `
                <div style='text-align:center'>
                    <i class='${widget.icon || 'fa fa-cube'}' style='font-size:24px;display:block;margin-bottom:5px'></i>
                    <div style='font-size:13px'>${widget.name}</div>
                </div>`;
            
            editor.BlockManager.add('widget-' + widget.slug, {
                label: labelHtml,
                category: 'Widgets',
                content: {
                    type: 'widget',
                    widgetSlug: widget.slug,
                    widgetId: widget.id,
                    name: widget.name,
                    attributes: { 'data-widget-type': 'widget' }
                }
            });
        });
    } catch (error) {
        console.error('Error loading widgets:', error);
    }

    // Detect widget drop into section
editor.on('component:add', function(component) {
    console.log('Component added:', component.get('type'), component.get('attributes'));
    
    // Only trigger for widgets
    if (component.get('type') === 'widget') {
        console.log('Widget detected, checking parent...');
        
        // Small delay to ensure DOM is ready
        setTimeout(() => {
            const parent = component.parent();
            console.log('Parent:', parent ? parent.get('type') : 'no parent');
            
            // Check if it's inside a section (might be nested in container/row/col)
            let currentParent = parent;
            let foundSection = false;
            
            while (currentParent) {
                const parentType = currentParent.get('type');
                const parentAttrs = currentParent.get('attributes') || {};
                
                if (parentType === 'section' || parentAttrs['data-section-type']) {
                    foundSection = true;
                    break;
                }
                currentParent = currentParent.parent();
            }
            
            if (foundSection) {
                console.log('Widget dropped in section, opening widget modal...');
                if (window.WidgetManager && window.WidgetManager.openWidgetModal) {
                    window.WidgetManager.openWidgetModal(component);
                }
            }
        }, 100);
    }
});
});

// Save section edit to backend
async function saveSectionEdit(sectionComponent) {
    const attrs = sectionComponent.get('attributes');
    const styles = sectionComponent.getStyle();
    const pageId = window.PAGE_ID;
    const sectionId = attrs['data-section-id'];
    if (!sectionId) return;
    const payload = {
        name: attrs['data-section-name'],
        background_color: styles['background-color'],
        padding: styles['padding'],
        margin: styles['margin'],
        css_classes: attrs['class'],
    };
    try {
        const res = await fetch(`/admin/api/pages/${pageId}/sections/${sectionId}`, {
            method: 'PUT',
            headers: { 
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': window.csrfToken
            },
            body: JSON.stringify(payload)
        });
        if (res.ok) {
            showSaveIndicator(true);
        } else {
            showSaveIndicator(false);
        }
    } catch (e) {
        showSaveIndicator(false);
    }
}

function showSaveIndicator(success) {
    let indicator = document.getElementById('section-save-indicator');
    if (!indicator) {
        indicator = document.createElement('div');
        indicator.id = 'section-save-indicator';
        indicator.style.position = 'fixed';
        indicator.style.top = '80px';
        indicator.style.right = '40px';
        indicator.style.zIndex = 9999;
        indicator.style.background = success ? '#28a745' : '#dc3545';
        indicator.style.color = '#fff';
        indicator.style.padding = '8px 16px';
        indicator.style.borderRadius = '20px';
        indicator.style.boxShadow = '0 2px 8px rgba(0,0,0,0.1)';
        document.body.appendChild(indicator);
    }
    indicator.style.background = success ? '#28a745' : '#dc3545';
    indicator.innerHTML = success
        ? '<i class="fa fa-check"></i> Saved!'
        : '<i class="fa fa-exclamation-triangle"></i> Error saving!';
    indicator.style.display = 'block';
    setTimeout(() => { indicator.style.display = 'none'; }, 1200);
}

// Helper function to get page ID - implement based on your setup
function getPageId() {
    // Option 1: From URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const pageId = urlParams.get('page_id') || urlParams.get('id');
    
    if (pageId) return pageId;
    
    // Option 2: From data attribute
    const container = document.getElementById('gjs');
    if (container) {
        const dataPageId = container.getAttribute('data-page-id');
        if (dataPageId) return dataPageId;
    }
    
    // Option 3: From path (e.g., /admin/pages/123/edit)
    const pathMatch = window.location.pathname.match(/\/pages\/(\d+)/);
    if (pathMatch) return pathMatch[1];
    
    // Option 4: From global variable
    if (window.currentPageId) return window.currentPageId;
    
    console.warn('Page ID not found');
    return null;
}