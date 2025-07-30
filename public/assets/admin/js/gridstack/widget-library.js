window.WidgetLibrary = {
    widgets: [],
    
    async init() {
        console.log('🔧 Initializing Widget Library...');
        await this.loadAvailableWidgets();
        this.renderWidgetLibrary();
        this.setupDragAndDrop();
        console.log('✅ Widget Library initialized');
    },

    // Add immediate initialization check
    checkAndInit() {
        // Check if we're already on the layout tab
        const activeTab = document.querySelector('#pageTab .nav-link.active');
        if (activeTab && activeTab.getAttribute('data-bs-target') === '#layout') {
            console.log('🔄 Layout tab is active, initializing Widget Library immediately...');
            this.init();
        } else {
            console.log('🔄 Layout tab not active, waiting for tab switch...');
        }
    },

    async loadAvailableWidgets() {
        try {
            const response = await fetch('/admin/api/widgets/available', {
                headers: {
                    'X-CSRF-TOKEN': window.GridStackPageBuilder?.config?.csrfToken || '',
                    'Accept': 'application/json'
                }
            });
            if (response.ok) {
                const data = await response.json();
                this.widgets = data.widgets || [];
                console.log('✅ Loaded widgets from API:', this.widgets.length);
            } else {
                console.warn('⚠️ API returned error:', response.status, 'Using mock data');
                this.loadMockWidgets();
            }
        } catch (e) {
            console.warn('⚠️ Error loading widgets, using mock data:', e);
            this.loadMockWidgets();
        }
    },

    loadMockWidgets() {
        // Mock widgets for testing - changed 'forms' to 'form' to match HTML container
        this.widgets = [
            { id: 1, name: 'Text Widget', slug: 'text-widget', category: 'content', label: 'Text Widget' },
            { id: 2, name: 'Image Widget', slug: 'image-widget', category: 'media', label: 'Image Widget' },
            { id: 3, name: 'Button Widget', slug: 'button-widget', category: 'content', label: 'Button Widget' },
            { id: 4, name: 'Counter Widget', slug: 'counter-widget', category: 'content', label: 'Counter Widget' },
            { id: 5, name: 'Gallery Widget', slug: 'gallery-widget', category: 'media', label: 'Gallery Widget' },
            { id: 6, name: 'Contact Form', slug: 'contact-form', category: 'form', label: 'Contact Form' },
            { id: 7, name: 'Newsletter', slug: 'newsletter', category: 'form', label: 'Newsletter' },
            { id: 8, name: 'Spacer', slug: 'spacer', category: 'layout', label: 'Spacer' }
        ];
        console.log('✅ Loaded mock widgets:', this.widgets.length);
    },

    renderWidgetLibrary() {
        const categories = {
            content: [],
            layout: [],
            media: [],
            form: []  // Changed from 'forms' to 'form' to match HTML container ID
        };
        
        this.widgets.forEach(widget => {
            const cat = (widget.category || '').toLowerCase();
            if (categories[cat]) {
                categories[cat].push(widget);
            } else {
                categories.content.push(widget);
            }
        });
        
        Object.keys(categories).forEach(cat => {
            const container = document.getElementById(`${cat}Widgets`);
            if (!container) {
                console.warn(`⚠️ Container not found: ${cat}Widgets`);
                return;
            }
            
            container.innerHTML = '';
            categories[cat].forEach(widget => {
                const el = this.createWidgetElement(widget);
                container.appendChild(el);
            });
        });
        
        console.log('✅ Widget library rendered');
    },

    createWidgetElement(widget) {
        const el = document.createElement('div');
        el.className = 'widget-item';
        el.setAttribute('draggable', 'true');
        el.setAttribute('data-widget-id', widget.id);
        el.setAttribute('data-widget-slug', widget.slug);
        el.innerHTML = `
            <span class="widget-item-icon"><i class="ri-apps-line"></i></span>
            <span class="widget-item-name">${widget.label || widget.name || 'Unknown Widget'}</span>
        `;
        
        el.addEventListener('dragstart', function(e) {
            // Ensure widget data is properly captured with correct field names
            const widgetData = {
                id: widget.id,
                name: widget.label || widget.name, // Use label as name
                slug: widget.slug,
                category: widget.category || 'General',
                label: widget.label || widget.name, // Keep label for compatibility
                type: widget.slug // Add type for compatibility
            };
            
            console.log('🔄 Dragging widget object:', widgetData);
            console.log('🔄 Widget name:', widgetData.name);
            console.log('🔄 Widget label:', widgetData.label);
            console.log('🔄 Widget id:', widgetData.id);
            
            e.dataTransfer.setData('text/plain', JSON.stringify(widgetData));
            el.classList.add('dragging');
            console.log('🔄 Dragging widget:', widgetData.name || widgetData.label || 'Unknown Widget');
        });
        
        el.addEventListener('dragend', function(e) {
            el.classList.remove('dragging');
        });
        
        return el;
    },

    setupDragAndDrop() {
        console.log('🔧 Widget library drag and drop setup - only handling dragstart');
        // Only setup dragstart events, let widget-manager.js handle drops
        console.log('✅ Widget library drag setup complete');
    }
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Check if we should initialize immediately
    if (window.WidgetLibrary) {
        window.WidgetLibrary.checkAndInit();
    }
}); 