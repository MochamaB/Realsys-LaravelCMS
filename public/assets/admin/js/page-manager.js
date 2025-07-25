// page-manager.js

// Create a namespace to avoid module issues
window.PageManager = window.PageManager || {};

/**
 * Convert a section (from API) to a GrapesJS component structure.
 */
async function sectionToComponent(section, pageId = null) {
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
    let sectionWidgets = [];
    
    try {
        // First, try to get section data with widgets from the sections API
        const sectionsResponse = await fetch(`/admin/api/pages/${pageId}/sections`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': window.csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        });
        
        if (sectionsResponse.ok) {
            const sectionsData = await sectionsResponse.json();
            const currentSection = sectionsData.sections?.find(s => s.id === section.id);
            if (currentSection && currentSection.widgets) {
                sectionWidgets = currentSection.widgets;
                console.log('✅ Found widgets for section:', sectionName, sectionWidgets.length);
            }
        }
        
        // Only load rendered HTML if we don't have widgets to preserve interactivity
        if (sectionWidgets.length === 0) {
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
        }
    } catch (error) {
        console.warn('Error loading section data:', error);
    }
    
    // If we have widgets data, create widget components; otherwise use HTML or placeholder
    if (sectionWidgets && sectionWidgets.length > 0) {
        const widgetComponents = sectionWidgets.map(widget => ({
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
        
        // Distribute widgets across columns based on section type
        let containerComponents = [];
        
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
        
        // Distribute widgets across columns
        const row = containerComponents[0];
        const columns = row.components;
        
        widgetComponents.forEach((widget, index) => {
            const columnIndex = index % columns.length;
            columns[columnIndex].components.push(widget);
        });
        
        return {
            tagName: 'div',
            attributes: sectionAttrs,
            components: [
                // Add section header for identification in designer
                {
                    tagName: 'div',
                    attributes: { 
                        class: 'section-designer-header',
                        style: 'background: #405189; color: white; padding: 8px 15px; font-size: 12px; font-weight: 500; border-radius: 4px 4px 0 0; margin-bottom: 10px; position: relative; z-index: 10;'
                    },
                    components: [
                        {
                            tagName: 'i',
                            attributes: { 
                                class: 'ri-layout-line',
                                style: 'margin-right: 8px;'
                            }
                        },
                        {
                            type: 'text',
                            content: `Section: ${sectionName} (${sectionWidgets.length} widgets)`
                        },
                        {
                            tagName: 'small',
                            attributes: { 
                                style: 'float: right; opacity: 0.8;'
                            },
                            components: [
                                {
                                    type: 'text',
                                    content: `ID: ${section.id}`
                                }
                            ]
                        }
                    ]
                },
                // Add the section content with widgets
                {
                    tagName: 'section',
                    attributes: { 
                        class: 'section-content',
                        style: 'position: relative;'
                    },
                    components: [
                        {
                            tagName: 'div',
                            attributes: { class: 'container' },
                            components: containerComponents
                        }
                    ]
                }
            ]
        };
    }
    
    // If we have actual HTML, use it directly with section header
    if (actualHTML) {
        return {
            tagName: 'div',
            attributes: sectionAttrs,
            components: [
                // Add section header for identification in designer
                {
                    tagName: 'div',
                    attributes: { 
                        class: 'section-designer-header',
                        style: 'background: #405189; color: white; padding: 8px 15px; font-size: 12px; font-weight: 500; border-radius: 4px 4px 0 0; margin-bottom: 10px; position: relative; z-index: 10;'
                    },
                    components: [
                        {
                            tagName: 'i',
                            attributes: { 
                                class: 'ri-layout-line',
                                style: 'margin-right: 8px;'
                            }
                        },
                        {
                            type: 'text',
                            content: `Section: ${sectionName}`
                        },
                        {
                            tagName: 'small',
                            attributes: { 
                                style: 'float: right; opacity: 0.8;'
                            },
                            components: [
                                {
                                    type: 'text',
                                    content: `ID: ${section.id}`
                                }
                            ]
                        }
                    ]
                },
                // Add the actual section content
                {
                    tagName: 'div',
                    attributes: { 
                        class: 'section-content',
                        style: 'position: relative;'
                    },
                    components: actualHTML
                }
            ]
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
 * Convert a section schema (from new API) to a GrapesJS component structure.
 */
async function sectionSchemaToComponent(sectionSchema, pageId = null) {
    console.log('Converting section schema to component:', sectionSchema);
    
    const sectionId = sectionSchema.id;
    const sectionName = sectionSchema.name || `Section ${sectionId}`;
    const sectionType = sectionSchema.type || 'default';
    
    let sectionAttrs = {
        'data-section-type': sectionType,
        'data-section-id': sectionId,
        'data-section-name': sectionName,
        class: `section section-${sectionType}`,
        style: 'min-height:200px; position: relative;'
    };
    
    // Create container components based on schema columns
    const containerComponents = [];
    
    if (sectionSchema.columns && sectionSchema.columns.length > 0) {
        // Create row container
        const rowComponent = {
            tagName: 'div',
            attributes: { class: 'row' },
            components: []
        };
        
        // Process each column from schema
        for (const columnData of sectionSchema.columns) {
            const columnComponent = {
                tagName: 'div',
                attributes: { 
                    class: columnData.class || 'col-12',
                    'data-column-id': columnData.id || 0
                },
                components: []
            };
            
            // Add widgets to column
            if (columnData.widgets && columnData.widgets.length > 0) {
                for (const widgetData of columnData.widgets) {
                    try {
                        const widgetComponent = await createWidgetComponentFromSchema(widgetData);
                        columnComponent.components.push(widgetComponent);
                    } catch (error) {
                        console.error('Error creating widget component:', error);
                        // Add error placeholder
                        columnComponent.components.push({
                            type: 'text',
                            content: `Error loading widget: ${widgetData.widget_name || 'Unknown'}`
                        });
                    }
                }
            } else {
                // Add drop zone placeholder
                columnComponent.components.push({
                    tagName: 'div',
                    attributes: { 
                        class: 'widget-drop-zone',
                        style: 'min-height: 60px; border: 2px dashed #e9ecef; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: #6c757d; font-size: 14px; margin: 10px 0;'
                    },
                    components: [{
                        type: 'text',
                        content: 'Drop widgets here'
                    }]
                });
            }
            
            rowComponent.components.push(columnComponent);
        }
        
        containerComponents.push(rowComponent);
    } else {
        // Default single column layout
        containerComponents.push({
            tagName: 'div',
            attributes: { class: 'row' },
            components: [{
                tagName: 'div',
                attributes: { class: 'col-12' },
                components: [{
                    type: 'text',
                    content: `${sectionName} - No widgets configured`
                }]
            }]
        });
    }
    
    return {
        tagName: 'div',
        attributes: sectionAttrs,
        components: [
            // Add section header for identification in designer
            {
                tagName: 'div',
                attributes: { 
                    class: 'section-designer-header',
                    style: 'background: #405189; color: white; padding: 8px 15px; font-size: 12px; font-weight: 500; border-radius: 4px 4px 0 0; margin-bottom: 10px; position: relative; z-index: 10;'
                },
                components: [
                    {
                        tagName: 'i',
                        attributes: { 
                            class: 'ri-layout-line',
                            style: 'margin-right: 8px;'
                        }
                    },
                    {
                        type: 'text',
                        content: `${sectionName} (${sectionSchema.meta?.widget_count || 0} widgets)`
                    },
                    {
                        tagName: 'small',
                        attributes: { 
                            style: 'float: right; opacity: 0.8;'
                        },
                        components: [
                            {
                                type: 'text',
                                content: `ID: ${sectionId} | Type: ${sectionType}`
                            }
                        ]
                    }
                ]
            },
            // Add the section content
            {
                tagName: 'section',
                attributes: { 
                    class: 'section-content',
                    style: 'position: relative;'
                },
                components: [
                    {
                        tagName: 'div',
                        attributes: { class: 'container' },
                        components: containerComponents
                    }
                ]
            }
        ]
    };
}

/**
 * Create a widget component from schema data
 */
async function createWidgetComponentFromSchema(widgetData) {
    console.log('Creating widget component from schema:', widgetData);
    
    const widgetId = widgetData.widget_id;
    const widgetSlug = widgetData.widget_type;
    const widgetName = widgetData.widget_name;
    const previewEndpoint = widgetData.preview_endpoint;
    
    // Create widget component structure
    const widgetComponent = {
        type: 'widget',
        widgetSlug: widgetSlug,
        widgetId: widgetId,
        name: widgetName,
        attributes: { 
            'data-widget-type': 'widget',
            'data-widget-slug': widgetSlug,
            'data-widget-id': widgetId,
            'data-widget-name': widgetName,
            'data-page-section-widget-id': widgetData.id,
            class: `widget-component ${widgetSlug}-widget`,
            style: 'position: relative; min-height: 50px; margin: 10px 0;'
        },
        components: []
    };
    
    // Try to load widget preview
    if (previewEndpoint) {
        try {
            console.log('🔄 Attempting to load widget preview:', {
                widgetName: widgetName,
                previewEndpoint: previewEndpoint
            });
            
            const previewResponse = await fetch(previewEndpoint, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': window.csrfToken
                }
            });
            
            console.log('📡 Preview response received:', {
                status: previewResponse.status,
                statusText: previewResponse.statusText,
                ok: previewResponse.ok
            });
            
            if (previewResponse.ok) {
                const previewData = await previewResponse.json();
                console.log('📦 Preview data parsed:', {
                    success: previewData.success,
                    hasHtml: !!previewData.html,
                    htmlLength: previewData.html ? previewData.html.length : 0,
                    dataKeys: Object.keys(previewData)
                });
                
                if (previewData.success && previewData.html) {
                    // Parse the HTML and inject it properly into GrapesJS
                    try {
                        console.log('🔧 Before injection - widget component:', {
                            type: widgetComponent.get('type'),
                            tagName: widgetComponent.get('tagName'),
                            componentsCount: widgetComponent.components().length,
                            attributes: widgetComponent.getAttributes()
                        });
                        
                        // Create a temporary container to parse the HTML
                        const tempDiv = document.createElement('div');
                        tempDiv.innerHTML = previewData.html;
                        
                        // Convert the parsed HTML to GrapesJS components
                        const htmlContent = tempDiv.innerHTML;
                        
                        // Clear existing components first
                        widgetComponent.components().reset();
                        
                        // Add the HTML content using GrapesJS methods
                        const newComponent = widgetComponent.components().add({
                            type: 'default',
                            tagName: 'div',
                            attributes: {
                                'data-widget-content': 'true',
                                'class': 'widget-preview-content'
                            },
                            content: htmlContent
                        });
                        
                        console.log('🔧 After injection - widget component:', {
                            componentsCount: widgetComponent.components().length,
                            newComponentType: newComponent ? newComponent.get('type') : 'none',
                            newComponentContent: newComponent ? newComponent.get('content') : 'none'
                        });
                        
                        // Force a view refresh
                        widgetComponent.view.render();
                        
                        console.log('✅ Loaded widget preview for:', widgetName, {
                            htmlLength: htmlContent.length,
                            preview: htmlContent.substring(0, 100) + '...',
                            componentsCount: widgetComponent.components().length
                        });
                    } catch (parseError) {
                        console.error('❌ Failed to parse widget HTML:', parseError);
                        throw new Error('Failed to parse widget HTML');
                    }
                } else {
                    console.warn('❌ Invalid preview response structure:', previewData);
                    throw new Error('Invalid preview response');
                }
            } else {
                const errorText = await previewResponse.text();
                console.error('❌ Preview API error response:', errorText);
                throw new Error(`Preview API returned ${previewResponse.status}`);
            }
        } catch (error) {
            console.warn('Failed to load widget preview:', error);
            // Fallback to placeholder
            widgetComponent.components = [{
                tagName: 'div',
                attributes: {
                    class: 'widget-placeholder',
                    style: 'padding: 20px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; text-align: center;'
                },
                components: [{
                    type: 'text',
                    content: `${widgetName} (Preview Loading...)`
                }]
            }];
        }
    } else {
        // No preview endpoint, use basic placeholder
        widgetComponent.components = [{
            tagName: 'div',
            attributes: {
                class: 'widget-placeholder',
                style: 'padding: 20px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; text-align: center;'
            },
            components: [{
                type: 'text',
                content: widgetName
            }]
        }];
    }
    
    return widgetComponent;
}

/**
 * Fetch sections from the API and load them into the GrapesJS editor.
 * @param {Object} editor - The GrapesJS editor instance
 * @param {string|number} pageId - The current page ID
 */
window.PageManager.loadSectionsToGrapesJS = async function(editor, pageId) {
    try {
        console.log(`Loading sections for page ID: ${pageId}`);
        
        // Use the new section schemas API endpoint
        const res = await fetch(`/admin/api/pages/${pageId}/sections/schemas`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': window.csrfToken
            }
        });
        
        if (!res.ok) {
            throw new Error(`HTTP error! status: ${res.status}`);
        }
        
        const data = await res.json();
        console.log('Section Schemas API Response:', data);
        
        const sections = data.schemas || data.data || [];
        
        if (!Array.isArray(sections)) {
            console.warn('Section schemas data is not an array:', sections);
            return;
        }
        
        if (sections.length === 0) {
            console.log('No sections found for this page');
            return;
        }
        
        // Process sections using the new schema format
        const components = [];
        for (const section of sections) {
            const component = await sectionSchemaToComponent(section, pageId);
            components.push(component);
        }
        console.log('Generated components from schemas:', components);
        
        // Clear existing content and add new components
        editor.setComponents(components);
        
        console.log(`Successfully loaded ${sections.length} sections from schemas`);
        
    } catch (error) {
        console.error('Error loading section schemas:', error);
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