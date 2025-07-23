// page-manager.js

// Create a namespace to avoid module issues
window.PageManager = window.PageManager || {};

/**
 * Convert a section (from API) to a GrapesJS component structure.
 */
async function sectionToComponent(section) {
    // Determine section type with fallback logic
    const sectionType = section.type || 
                       (section.template_section && section.template_section.section_type) || 
                       'default';
    
    const sectionName = section.name || 
                       (section.template_section && section.template_section.name) || 
                       sectionType;
    
    let sectionAttrs = {
        'data-section-type': sectionType,
        'data-section-id': section.id, // Add the section ID for widget saving
        class: `section section-${sectionType}`,
        style: 'min-height:200px;'
    };
    
    // Try to fetch actual rendered HTML for this section
    let actualHTML = null;
    try {
        const response = await fetch(`/admin/api/sections/${section.id}/render`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': window.csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        });
        
        if (response.ok) {
            const data = await response.json();
            if (data.success && data.html) {
                actualHTML = data.html;
                console.log('✅ Loaded actual HTML for section:', sectionName);
            }
        } else {
            console.warn('Failed to load section HTML:', response.status);
        }
    } catch (error) {
        console.warn('Error loading section HTML:', error);
    }
    
    // If we have actual HTML, use it directly
    if (actualHTML) {
        return {
            tagName: 'div',
            attributes: sectionAttrs,
            components: actualHTML
        };
    }
    
    // Otherwise, fall back to the structured approach
    // Base container structure
    let containerComponents = [];
    
    // Handle different section layouts
    switch (sectionType) {
        case 'multi-column':
            containerComponents = [
                {
                    tagName: 'div',
                    attributes: { class: 'row' },
                    components: [
                        {
                            tagName: 'div',
                            attributes: { class: 'col-md-6' },
                            components: []
                        },
                        {
                            tagName: 'div',
                            attributes: { class: 'col-md-6' },
                            components: []
                        }
                    ]
                }
            ];
            break;
            
        case 'sidebar-left':
            containerComponents = [
                {
                    tagName: 'div',
                    attributes: { class: 'row' },
                    components: [
                        {
                            tagName: 'div',
                            attributes: { class: 'col-md-3' },
                            components: []
                        },
                        {
                            tagName: 'div',
                            attributes: { class: 'col-md-9' },
                            components: []
                        }
                    ]
                }
            ];
            break;
            
        case 'sidebar-right':
            containerComponents = [
                {
                    tagName: 'div',
                    attributes: { class: 'row' },
                    components: [
                        {
                            tagName: 'div',
                            attributes: { class: 'col-md-9' },
                            components: []
                        },
                        {
                            tagName: 'div',
                            attributes: { class: 'col-md-3' },
                            components: []
                        }
                    ]
                }
            ];
            break;
            
        default: // full-width, default, etc.
            containerComponents = [
                {
                    tagName: 'div',
                    attributes: { class: 'row' },
                    components: [
                        {
                            tagName: 'div',
                            attributes: { class: 'col-12' },
                            components: []
                        }
                    ]
                }
            ];
            break;
    }
    
    // Add widgets to the appropriate columns
    if (section.widgets && section.widgets.length > 0) {
        const widgetComponents = section.widgets.map(widget => ({
            type: 'widget',
            widgetSlug: widget.slug,
            widgetId: widget.id,
            name: widget.name || widget.type,
            attributes: { 
                'data-widget-type': 'widget',
                'data-widget-slug': widget.slug,
                'data-widget-id': widget.id
            }
        }));
        
        // Distribute widgets across columns
        const row = containerComponents[0];
        const columns = row.components;
        
        widgetComponents.forEach((widget, index) => {
            const columnIndex = index % columns.length;
            columns[columnIndex].components.push(widget);
        });
    } else {
        // Add placeholder text if no widgets
        const row = containerComponents[0];
        const firstColumn = row.components[0];
        firstColumn.components.push({
            type: 'text', 
            content: `${sectionName} - Drop widgets here`
        });
    }
    
    return {
        tagName: 'section',
        attributes: sectionAttrs,
        components: [
            {
                tagName: 'div',
                attributes: { class: 'container' },
                components: containerComponents
            }
        ]
    };
}

/**
 * Fetch sections from the API and load them into the GrapesJS editor.
 * @param {Object} editor - The GrapesJS editor instance
 * @param {string|number} pageId - The current page ID
 */
window.PageManager.loadSectionsToGrapesJS = async function(editor, pageId) {
    try {
        console.log(`Loading sections for page ID: ${pageId}`);
        
        const res = await fetch(`/admin/api/pages/${pageId}/sections`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': window.csrfToken
            }
        });
        
        if (!res.ok) {
            throw new Error(`HTTP error! status: ${res.status}`);
        }
        
        const data = await res.json();
        console.log('API Response:', data);
        
        const sections = data.sections || data.data || [];
        
        if (!Array.isArray(sections)) {
            console.warn('Sections data is not an array:', sections);
            return;
        }
        
        if (sections.length === 0) {
            console.log('No sections found for this page');
            return;
        }
        
        // Process sections asynchronously to load actual HTML
        const components = [];
        for (const section of sections) {
            const component = await sectionToComponent(section);
            components.push(component);
        }
        console.log('Generated components:', components);
        
        // Clear existing content and add new components
        editor.setComponents(components);
        
        console.log(`Successfully loaded ${sections.length} sections`);
        
    } catch (error) {
        console.error('Error loading sections:', error);
        throw error;
    }
};

/**
 * Save the current editor content back to the API
 * @param {Object} editor - The GrapesJS editor instance
 * @param {string|number} pageId - The current page ID
 */
window.PageManager.saveSectionsFromGrapesJS = async function(editor, pageId) {
    try {
        const components = editor.getComponents();
        const sections = [];
        
        components.forEach(component => {
            if (component.get('tagName') === 'section') {
                const sectionType = component.get('attributes')['data-section-type'];
                const widgets = [];
                
                // Extract widgets from the section
                component.find('[data-widget-type="widget"]').forEach(widgetComponent => {
                    const attrs = widgetComponent.get('attributes');
                    widgets.push({
                        slug: attrs['data-widget-slug'],
                        id: attrs['data-widget-id'],
                        name: widgetComponent.get('name')
                    });
                });
                
                sections.push({
                    type: sectionType,
                    widgets: widgets,
                    // Add other section properties as needed
                });
            }
        });
        
        const response = await fetch(`/admin/api/pages/${pageId}/sections`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': window.csrfToken
            },
            body: JSON.stringify({ sections })
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        console.log('Sections saved successfully');
        
    } catch (error) {
        console.error('Error saving sections:', error);
        throw error;
    }
};

/**
 * Render a widget with its actual HTML content
 * @param {number} widgetId - The widget ID
 * @returns {Promise<string>} The rendered HTML
 */
window.PageManager.renderWidget = async function(widgetId) {
    try {
        const response = await fetch(`/admin/api/widgets/${widgetId}/render`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': window.csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        });
        
        if (response.ok) {
            const data = await response.json();
            if (data.success && data.html) {
                console.log('✅ Loaded actual HTML for widget ID:', widgetId);
                return data.html;
            }
        } else {
            console.warn('Failed to load widget HTML:', response.status);
        }
    } catch (error) {
        console.warn('Error loading widget HTML:', error);
    }
    
    // Fallback HTML
    return `<div class="widget-placeholder">
        <h5>Widget (ID: ${widgetId})</h5>
        <p>Loading widget content...</p>
    </div>`;
};

/**
 * Update a widget component with its actual rendered HTML
 * @param {Object} component - The GrapesJS component
 * @param {number} widgetId - The widget ID
 */
window.PageManager.updateWidgetHTML = async function(component, widgetId) {
    const html = await window.PageManager.renderWidget(widgetId);
    component.components(html);
    console.log('Updated widget component with actual HTML');
};