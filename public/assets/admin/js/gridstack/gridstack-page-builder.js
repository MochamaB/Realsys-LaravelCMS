/**
 * GridStack Page Builder Core
 * Main controller for the page builder functionality
 */
window.GridStackPageBuilder = {
    config: {},
    gridStacks: {},
    selectedWidget: null,

    /**
     * Initialize the page builder
     */
    async init(config) {
        console.log('🚀 Initializing GridStack Page Builder...');
        
        this.config = config;
        
        // For now, just initialize a single demo section for testing
        this.renderDemoSection();
        
        console.log('✅ GridStack Page Builder initialized');
    },

    /**
     * Render demo section for testing
     */
    renderDemoSection() {
        const container = document.getElementById('pageSectionsContainer');
        container.innerHTML = '';
        
        // Create a single section for demo
        const sectionDiv = document.createElement('div');
        sectionDiv.className = 'page-section';
        sectionDiv.setAttribute('data-section-id', 'demo');
        
        sectionDiv.innerHTML = `
            <div class="section-header">
                <h6 class="section-title">Demo Section</h6>
                <div class="section-controls">
                    <button class="section-control-btn" title="Edit Section">
                        <i class="ri-settings-line"></i>
                    </button>
                </div>
            </div>
            <div class="section-content">
                <div class="section-grid-stack" data-section-id="demo">
                    <div class="widget-drop-zone">
                        <i class="ri-drag-drop-line"></i>
                        <span>Drop widgets here</span>
                    </div>
                </div>
            </div>
        `;
        
        container.appendChild(sectionDiv);
        
        // Initialize GridStack for this section
        const gridElement = sectionDiv.querySelector('.section-grid-stack');
        const grid = GridStack.init({
            cellHeight: 80,
            verticalMargin: 10,
            horizontalMargin: 10,
            column: 12,
            float: true,
            resizable: { handles: 'se, sw' },
            acceptWidgets: true,
            animate: true
        }, gridElement);
        
        // Store reference
        this.gridStacks['demo'] = grid;
        gridElement.gridStackInstance = grid;
        
        // Setup grid events
        grid.on('added', (event, items) => {
            items.forEach(item => {
                const element = item.el;
                element.addEventListener('click', (e) => {
                    e.stopPropagation();
                    this.selectWidget(element);
                });
            });
            
            // Hide drop zone when widgets are added
            gridElement.classList.add('has-widgets');
        });
        
        grid.on('removed', (event, items) => {
            // Show drop zone if no widgets left
            if (grid.getGridItems().length === 0) {
                gridElement.classList.remove('has-widgets');
            }
        });
    },

    /**
     * Add placeholder widget to section (called from WidgetLibrary)
     */
    addPlaceholderWidgetToSection(sectionGrid, widgetData) {
        const grid = sectionGrid.gridStackInstance;
        if (!grid) {
            console.error('❌ No GridStack instance found for section');
            return;
        }

        console.log('🔧 Adding placeholder widget:', widgetData);

        // Get the widget name (try both label and name properties)
        const widgetName = widgetData.label || widgetData.name || 'Unknown Widget';

        // Create grid item with placeholder content
        const gridItem = {
            x: 0, 
            y: 0, 
            w: 6, 
            h: 3,
            id: `widget-${Date.now()}`,
            content: `<div class="widget-loading">Loading ${widgetName}...</div>`
        };

        // Add to GridStack first
        const addedElement = grid.addWidget(gridItem);
        
        // Set data attributes on the grid item element
        addedElement.setAttribute('data-widget-id', widgetData.id);
        addedElement.setAttribute('data-widget-slug', widgetData.slug);
        addedElement.setAttribute('data-widget-name', widgetName);

        // Now set the innerHTML of the content area
        const contentDiv = addedElement.querySelector('.grid-stack-item-content');
        if (contentDiv) {
            contentDiv.innerHTML = `
                <div class="widget-preview-container" data-widget-type="${widgetData.slug}">
                    <div class="widget-preview-content">
                        <div class="text-center p-3">
                            <i class="ri-apps-line fs-1 text-muted"></i>
                            <h6>${widgetName}</h6>
                            <small class="text-muted">Widget placeholder</small>
                        </div>
                    </div>
                    <div class="widget-preview-overlay">
                        <div class="widget-controls">
                            <button class="widget-control-btn" onclick="window.WidgetManager.editWidget('${addedElement.id}')">
                                <i class="ri-edit-line"></i>
                            </button>
                            <button class="widget-control-btn" onclick="window.WidgetManager.duplicateWidget('${addedElement.id}')">
                                <i class="ri-file-copy-line"></i>
                            </button>
                            <button class="widget-control-btn" onclick="window.WidgetManager.deleteWidget('${addedElement.id}')">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }

        console.log('✅ Widget placeholder added to grid:', {
            id: addedElement.id,
            widgetId: widgetData.id,
            widgetName: widgetName,
            slug: widgetData.slug
        });
    },

    /**
     * Select widget (basic implementation for testing)
     */
    selectWidget(element) {
        // Remove previous selection
        document.querySelectorAll('.grid-stack-item-selected').forEach(el => {
            el.classList.remove('grid-stack-item-selected');
        });

        // Add selection to current element
        element.classList.add('grid-stack-item-selected');
        this.selectedWidget = element;

        // Debug: Log all widget data attributes
        const widgetData = {
            id: element.id,
            widgetId: element.getAttribute('data-widget-id'),
            widgetSlug: element.getAttribute('data-widget-slug'),
            widgetName: element.getAttribute('data-widget-name'),
            pageSectionWidgetId: element.getAttribute('data-page-section-widget-id')
        };

        console.log('📌 Widget selected:', widgetData);
    }
}; 