/**
 * Live Preview Sidebar Manager
 * Handles sidebar functionality specifically for the Live Preview tab
 */
class LivePreviewSidebar {
    constructor() {
        this.initialized = false;
        this.sectionsLoaded = false;
        this.widgetsLoaded = false;
    }

    /**
     * Initialize Live Preview sidebar
     */
    init() {
        console.log('🔧 Initializing Live Preview Sidebar...');
        
        this.ensureSidebarExpanded();
        this.loadSections();
        this.loadWidgets();
        this.setupSidebarInteractions();
        
        this.initialized = true;
        console.log('✅ Live Preview Sidebar initialized');
    }

    /**
     * Ensure left sidebar is expanded for Live Preview
     */
    ensureSidebarExpanded() {
        const leftSidebar = document.getElementById('leftSidebarContainer');
        if (leftSidebar && leftSidebar.classList.contains('collapsed')) {
            leftSidebar.classList.remove('collapsed');
            console.log('✅ Left sidebar expanded for Live Preview');
        }
    }

    /**
     * Collapse left sidebar
     */
    collapseSidebar() {
        const leftSidebar = document.getElementById('leftSidebarContainer');
        if (leftSidebar && !leftSidebar.classList.contains('collapsed')) {
            leftSidebar.classList.add('collapsed');
            console.log('✅ Left sidebar collapsed');
        }
    }

    /**
     * Expand left sidebar
     */
    expandSidebar() {
        const leftSidebar = document.getElementById('leftSidebarContainer');
        if (leftSidebar && leftSidebar.classList.contains('collapsed')) {
            leftSidebar.classList.remove('collapsed');
            console.log('✅ Left sidebar expanded');
        }
    }

    /**
     * Load sections for Live Preview
     */
    loadSections() {
        const sectionsContainer = document.getElementById('sectionsGrid');
        if (!sectionsContainer) {
            console.warn('⚠️ Sections container not found');
            return;
        }

        // Clear any existing "Live Preview Mode" messages
        sectionsContainer.innerHTML = '';

        // Load section templates
        const sections = this.getSectionTemplates();
        sections.forEach(section => {
            const sectionElement = this.createSectionElement(section);
            sectionsContainer.appendChild(sectionElement);
        });

        this.sectionsLoaded = true;
        console.log('✅ Sections loaded for Live Preview:', sections.length);
    }

    /**
     * Load widgets for Live Preview
     */
    loadWidgets() {
        const widgetsContainer = document.getElementById('themeWidgetsGrid');
        if (!widgetsContainer) {
            console.warn('⚠️ Widgets container not found');
            return;
        }

        // Clear any existing "Live Preview Mode" messages
        widgetsContainer.innerHTML = '';

        // Load widget library
        const widgets = this.getWidgetLibrary();
        widgets.forEach(widget => {
            const widgetElement = this.createWidgetElement(widget);
            widgetsContainer.appendChild(widgetElement);
        });

        this.widgetsLoaded = true;
        console.log('✅ Widgets loaded for Live Preview:', widgets.length);
    }

    /**
     * Get section templates for Live Preview
     */
    getSectionTemplates() {
        return [
            {
                id: 'full-width',
                name: 'Full Width',
                description: 'Full width section',
                icon: 'ri-layout-line',
                content: '<section class="cms-section full-width-section py-5" data-section-type="full-width"><div class="container-fluid"><div class="row"><div class="col-12"><h2>Full Width Section</h2><p>This is a full width section that spans the entire viewport.</p></div></div></div></section>'
            },
            {
                id: 'two-columns',
                name: 'Two Columns',
                description: 'Two column layout',
                icon: 'ri-layout-2-line',
                content: '<section class="cms-section two-columns-section py-5" data-section-type="two-columns"><div class="container"><div class="row"><div class="col-md-6"><h3>Column 1</h3><p>Left column content goes here.</p></div><div class="col-md-6"><h3>Column 2</h3><p>Right column content goes here.</p></div></div></div></section>'
            },
            {
                id: 'three-columns',
                name: 'Three Columns',
                description: 'Three column layout',
                icon: 'ri-layout-3-line',
                content: '<section class="cms-section three-columns-section py-5" data-section-type="three-columns"><div class="container"><div class="row"><div class="col-md-4"><h4>Column 1</h4><p>First column content.</p></div><div class="col-md-4"><h4>Column 2</h4><p>Second column content.</p></div><div class="col-md-4"><h4>Column 3</h4><p>Third column content.</p></div></div></div></section>'
            },
            {
                id: 'hero-section',
                name: 'Hero Section',
                description: 'Hero banner section',
                icon: 'ri-image-line',
                content: '<section class="cms-section hero-section py-5 bg-primary text-white" data-section-type="hero"><div class="container"><div class="row align-items-center"><div class="col-lg-6"><h1 class="display-4">Hero Title</h1><p class="lead">Hero description text goes here.</p><a href="#" class="btn btn-light btn-lg">Call to Action</a></div><div class="col-lg-6"><img src="/themes/miata/images/hero-placeholder.jpg" class="img-fluid" alt="Hero Image"></div></div></div></section>'
            }
        ];
    }

    /**
     * Get widget library for Live Preview
     */
    getWidgetLibrary() {
        return [
            {
                id: 'text-widget',
                name: 'Text Widget',
                slug: 'text-widget',
                category: 'content',
                icon: 'ri-text',
                description: 'Add text content'
            },
            {
                id: 'image-widget',
                name: 'Image Widget',
                slug: 'image-widget',
                category: 'media',
                icon: 'ri-image-line',
                description: 'Add images'
            },
            {
                id: 'button-widget',
                name: 'Button Widget',
                slug: 'button-widget',
                category: 'content',
                icon: 'ri-button-line',
                description: 'Add buttons'
            },
            {
                id: 'counter-widget',
                name: 'Counter Widget',
                slug: 'counter-widget',
                category: 'content',
                icon: 'ri-number-1',
                description: 'Add animated counters'
            },
            {
                id: 'gallery-widget',
                name: 'Gallery Widget',
                slug: 'gallery-widget',
                category: 'media',
                icon: 'ri-gallery-line',
                description: 'Add image galleries'
            },
            {
                id: 'contact-form',
                name: 'Contact Form',
                slug: 'contact-form',
                category: 'form',
                icon: 'ri-mail-line',
                description: 'Add contact forms'
            },
            {
                id: 'newsletter',
                name: 'Newsletter',
                slug: 'newsletter',
                category: 'form',
                icon: 'ri-newsletter-line',
                description: 'Add newsletter signup'
            },
            {
                id: 'spacer',
                name: 'Spacer',
                slug: 'spacer',
                category: 'layout',
                icon: 'ri-space',
                description: 'Add spacing'
            }
        ];
    }

    /**
     * Create section element for sidebar
     */
    createSectionElement(section) {
        const element = document.createElement('div');
        element.className = 'section-item';
        element.setAttribute('draggable', 'true');
        element.setAttribute('data-section-id', section.id);
        element.setAttribute('data-section-type', section.id);
        
        element.innerHTML = `
            <div class="section-item-content">
                <div class="section-item-icon">
                    <i class="${section.icon}"></i>
                </div>
                <div class="section-item-info">
                    <div class="section-item-name">${section.name}</div>
                    <div class="section-item-description">${section.description}</div>
                </div>
            </div>
        `;

        // Add drag functionality
        element.addEventListener('dragstart', (e) => {
            const dragData = {
                type: 'section',
                id: section.id,
                name: section.name,
                content: section.content
            };
            e.dataTransfer.setData('text/plain', JSON.stringify(dragData));
            console.log('🔄 Section drag started:', section.name);
        });

        return element;
    }

    /**
     * Create widget element for sidebar
     */
    createWidgetElement(widget) {
        const element = document.createElement('div');
        element.className = 'widget-item';
        element.setAttribute('draggable', 'true');
        element.setAttribute('data-widget-id', widget.id);
        element.setAttribute('data-widget-slug', widget.slug);
        
        element.innerHTML = `
            <div class="widget-item-content">
                <div class="widget-item-icon">
                    <i class="${widget.icon}"></i>
                </div>
                <div class="widget-item-info">
                    <div class="widget-item-name">${widget.name}</div>
                    <div class="widget-item-description">${widget.description}</div>
                </div>
            </div>
        `;

        // Add drag functionality
        element.addEventListener('dragstart', (e) => {
            const dragData = {
                type: 'widget',
                id: widget.id,
                name: widget.name,
                slug: widget.slug,
                category: widget.category
            };
            e.dataTransfer.setData('text/plain', JSON.stringify(dragData));
            console.log('🔄 Widget drag started:', widget.name);
        });

        return element;
    }

    /**
     * Setup sidebar interactions
     */
    setupSidebarInteractions() {
        // Expand sections by default in Live Preview
        const sectionsCollapse = document.getElementById('sectionsCollapse');
        if (sectionsCollapse && !sectionsCollapse.classList.contains('show')) {
            sectionsCollapse.classList.add('show');
            const sectionsLink = document.querySelector('[href="#sectionsCollapse"]');
            if (sectionsLink) {
                sectionsLink.classList.remove('collapsed');
                sectionsLink.setAttribute('aria-expanded', 'true');
            }
        }

        // Expand widgets by default in Live Preview
        const widgetsCollapse = document.getElementById('themeWidgetsCollapse');
        if (widgetsCollapse && !widgetsCollapse.classList.contains('show')) {
            widgetsCollapse.classList.add('show');
            const widgetsLink = document.querySelector('[href="#themeWidgetsCollapse"]');
            if (widgetsLink) {
                widgetsLink.classList.remove('collapsed');
                widgetsLink.setAttribute('aria-expanded', 'true');
            }
        }

        console.log('✅ Sidebar interactions setup complete');
    }

    /**
     * Reset sidebar for other tabs
     */
    reset() {
        // This method can be called when switching away from Live Preview
        // to reset any Live Preview specific states
        console.log('🔄 Resetting Live Preview sidebar');
    }
}

// Create global instance
window.LivePreviewSidebar = new LivePreviewSidebar();
