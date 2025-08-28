/**
 * Preview Helper Functions
 * 
 * JavaScript helpers that get injected into the preview iframe
 * These functions enable widget selection and interaction in the preview
 */

// Initialize preview helpers when document is ready
(function() {
    'use strict';
    
    // Check if we're in an iframe (preview mode)
    const isInIframe = window !== window.parent;
    
    if (isInIframe) {
        // Initialize when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                // Add preview mode indicator to body
                if (document.body) {
                    document.body.setAttribute('data-preview-mode', 'true');
                }
                initializePreviewHelpers();
            });
        } else {
            // Add preview mode indicator to body
            if (document.body) {
                document.body.setAttribute('data-preview-mode', 'true');
            }
            initializePreviewHelpers();
        }
    }
    
    function initializePreviewHelpers() {
        console.log('🎨 Initializing preview helpers');
        
        // First, map the page structure to DOM elements
        if (window.previewPageStructure) {
            mapStructureToDOM();
        } else {
            console.warn('⚠️ No page structure data found');
        }
        
        // Setup widget interactions
        setupWidgetInteractions();
        
        // Setup section interactions
        setupSectionInteractions();
        
        // Setup keyboard shortcuts
        setupKeyboardShortcuts();
        
        // Setup resize observer for responsive updates
        setupResizeObserver();
        
        // Listen for messages from parent
        setupMessageListener();
        
        console.log('✅ Preview helpers initialized');
    }
    
    function mapStructureToDOM() {
        // The universal components already provide the data attributes we need!
        // We just need to enhance them for preview functionality
        
        const structure = window.previewPageStructure;
        console.log('🗺️ Using existing universal component data attributes...');
        
        // Enhance sections with preview data
        const sections = document.querySelectorAll('section[data-section-id]');
        sections.forEach((section, idx) => {
            const sectionId = section.getAttribute('data-section-id');
            const structureSection = structure.sections.find(s => s.id == sectionId);
            
            if (structureSection) {
                section.setAttribute('data-preview-section', structureSection.id);
                section.setAttribute('data-section-name', structureSection.name);
                
                console.log(`📦 Enhanced section "${structureSection.name}" (ID: ${structureSection.id})`);
            }
        });
        
        // Enhance widgets with preview data
        const widgets = document.querySelectorAll('div[data-page-section-widget-id]');
        widgets.forEach((widget, idx) => {
            const instanceId = widget.getAttribute('data-page-section-widget-id');
            const widgetId = widget.getAttribute('data-widget-id');
            
            // Find this widget in the structure
            let structureWidget = null;
            let sectionId = null;
            
            structure.sections.forEach(section => {
                const foundWidget = section.widgets.find(w => w.id == instanceId);
                if (foundWidget) {
                    structureWidget = foundWidget;
                    sectionId = section.id;
                }
            });
            
            if (structureWidget) {
                // Add preview-specific attributes using the CORRECT PageSectionWidget ID
                widget.setAttribute('data-preview-widget', instanceId); // This is the key fix!
                widget.setAttribute('data-widget-instance', instanceId);
                widget.setAttribute('data-section-id', sectionId);
                widget.setAttribute('data-widget-name', structureWidget.name);
                widget.setAttribute('data-widget-icon', structureWidget.icon || 'ri-puzzle-line');
                
                console.log(`🎯 Enhanced widget "${structureWidget.name}" (instance: ${instanceId}, widget: ${widgetId})`);
            }
        });
        
        console.log(`✅ Enhanced ${widgets.length} widgets and ${sections.length} sections using existing data attributes`);
    }
    
    function setupWidgetInteractions() {
        const widgets = document.querySelectorAll('[data-preview-widget]');
        
        widgets.forEach(widget => {
            // Make widget clickable
            widget.style.cursor = 'pointer';
            widget.setAttribute('tabindex', '0'); // Make focusable
            
            // Click handler
            widget.addEventListener('click', function(e) {
                // Check if click is on toolbar actions (in the future when we add real buttons)
                if (e.target.closest('.preview-toolbar-action')) {
                    e.preventDefault();
                    e.stopPropagation();
                    handleToolbarAction(e.target, this);
                    return;
                }
                
                e.preventDefault();
                e.stopPropagation();
                selectWidget(
                    this.dataset.previewWidget, // PageSectionWidget instance ID
                    this.dataset.widgetId, // Original widget ID
                    this.dataset.sectionId, // Section ID for context
                    this.dataset.widgetName // Widget name for display
                );
            });
            
            // Keyboard handler
            widget.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    selectWidget(
                        this.dataset.previewWidget,
                        this.dataset.widgetId,
                        this.dataset.sectionId,
                        this.dataset.widgetName
                    );
                }
            });
            
            // Hover effects - using box-shadow now
            widget.addEventListener('mouseenter', function() {
                if (!this.classList.contains('preview-highlighted')) {
                    this.style.boxShadow = '0 0 0 2px #0d6efd, 0 0 0 4px rgba(13, 110, 253, 0.2)';
                }
            });
            
            widget.addEventListener('mouseleave', function() {
                if (!this.classList.contains('preview-highlighted')) {
                    this.style.boxShadow = '';
                }
            });
            
            // Focus effects - using box-shadow
            widget.addEventListener('focus', function() {
                this.style.boxShadow = '0 0 0 3px #0d6efd, 0 0 0 5px rgba(13, 110, 253, 0.3)';
            });
            
            widget.addEventListener('blur', function() {
                if (!this.classList.contains('preview-highlighted')) {
                    this.style.boxShadow = '';
                }
            });
        });
        
        console.log(`🎯 Setup interactions for ${widgets.length} widgets`);
    }
    
    function setupSectionInteractions() {
        const sections = document.querySelectorAll('[data-preview-section]');
        
        sections.forEach(section => {
            section.addEventListener('click', function(e) {
                // Only trigger if clicking the section itself, not a child widget
                if (e.target === this || !e.target.closest('[data-preview-widget]')) {
                    e.preventDefault();
                    e.stopPropagation();
                    selectSection(
                        this.dataset.previewSection, // Section ID
                        this.dataset.sectionName // Section name for display
                    );
                }
            });
            
            // Don't add any empty section indicators - let the templates handle empty states
        });
        
        console.log(`📦 Setup interactions for ${sections.length} sections`);
    }
    
    function setupKeyboardShortcuts() {
        document.addEventListener('keydown', function(e) {
            // ESC to deselect
            if (e.key === 'Escape') {
                deselectAll();
            }
            
            // Arrow keys to navigate between widgets
            if (['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(e.key)) {
                const highlighted = document.querySelector('.preview-highlighted');
                if (highlighted) {
                    e.preventDefault();
                    navigateWidgets(highlighted, e.key);
                }
            }
            
            // Tab to focus next widget
            if (e.key === 'Tab' && !e.shiftKey) {
                const widgets = Array.from(document.querySelectorAll('[data-preview-widget]'));
                const currentIndex = widgets.findIndex(w => w.classList.contains('preview-highlighted'));
                
                if (currentIndex >= 0 && currentIndex < widgets.length - 1) {
                    e.preventDefault();
                    const widget = widgets[currentIndex + 1];
                    selectWidget(
                        widget.dataset.previewWidget,
                        widget.dataset.widgetId,
                        widget.dataset.sectionId,
                        widget.dataset.widgetName
                    );
                    widget.focus();
                }
            }
        });
        
        console.log('⌨️ Keyboard shortcuts enabled');
    }
    
    function setupResizeObserver() {
        if ('ResizeObserver' in window) {
            const resizeObserver = new ResizeObserver(entries => {
                // Notify parent about size changes
                parent.postMessage({
                    type: 'preview-resized',
                    width: document.body.scrollWidth,
                    height: document.body.scrollHeight
                }, '*');
            });
            
            resizeObserver.observe(document.body);
        }
    }
    
    function setupMessageListener() {
        window.addEventListener('message', function(event) {
            // Verify message is from parent
            if (event.source !== window.parent) return;
            
            const { type, data } = event.data;
            
            switch (type) {
                case 'highlight-widget':
                    highlightWidget(data.widgetId);
                    break;
                    
                case 'deselect-all':
                    deselectAll();
                    break;
                    
                case 'device-changed':
                    handleDeviceChange(data.device, data.settings);
                    break;
                    
                case 'scroll-to-widget':
                    scrollToWidget(data.widgetId);
                    break;
                    
                case 'update-widget-state':
                    updateWidgetState(data.widgetId, data.state);
                    break;
            }
        });
        
        console.log('📢 Message listener setup');
    }
    
    function selectWidget(instanceId, widgetId, sectionId, widgetName) {
        // Clear previous selections
        deselectAll();
        
        // Highlight selected widget
        highlightWidget(instanceId);
        
        // Notify parent with correct PageSectionWidget instance ID
        parent.postMessage({
            type: 'widget-selected',
            data: { 
                instanceId: instanceId, // PageSectionWidget ID for editing
                widgetId: widgetId, // Original widget ID for reference
                sectionId: sectionId, // Section context
                widgetName: widgetName // Display name
            }
        }, '*');
        
        console.log(`✓ Widget selected: ${widgetName} (instance: ${instanceId}, widget: ${widgetId}, section: ${sectionId})`);
    }
    
    function selectSection(sectionId, sectionName) {
        // Clear previous selections
        deselectAll();
        
        // Highlight selected section
        highlightSection(sectionId);
        
        // Notify parent
        parent.postMessage({
            type: 'section-selected',
            data: { 
                sectionId: sectionId,
                sectionName: sectionName 
            }
        }, '*');
        
        console.log(`✓ Section selected: ${sectionName} (ID: ${sectionId})`);
    }
    
    function highlightWidget(instanceId) {
        const widget = document.querySelector(`[data-preview-widget="${instanceId}"]`);
        if (widget) {
            // Remove existing highlights
            document.querySelectorAll('.preview-highlighted').forEach(el => {
                el.classList.remove('preview-highlighted');
                el.style.boxShadow = '';
            });
            
            // Add highlight to selected widget
            widget.classList.add('preview-highlighted');
            
            // Scroll into view with some top offset to account for toolbar
            widget.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'center',
                inline: 'center'
            });
        }
    }
    
    function highlightSection(sectionId) {
        const section = document.querySelector(`[data-preview-section="${sectionId}"]`);
        if (section) {
            // Remove existing highlights
            document.querySelectorAll('.preview-highlighted').forEach(el => {
                el.classList.remove('preview-highlighted');
                el.style.boxShadow = '';
                // Remove existing toolbar buttons
                const existingButtons = el.querySelector('.section-toolbar-buttons');
                if (existingButtons) {
                    existingButtons.remove();
                }
            });
            
            // Add highlight to selected section
            section.classList.add('preview-highlighted');
            
            // Create toolbar buttons
            createSectionToolbarButtons(section, sectionId);
            
            // Scroll into view with some top offset to account for toolbar
            section.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'center',
                inline: 'center'
            });
        }
    }
    
    function createSectionToolbarButtons(section, sectionId) {
        // Create toolbar buttons container
        const toolbarButtons = document.createElement('div');
        toolbarButtons.className = 'section-toolbar-buttons';
        
        // Add Widget Button (Primary action)
        const addWidgetBtn = document.createElement('button');
        addWidgetBtn.className = 'section-toolbar-btn btn-primary';
        addWidgetBtn.innerHTML = '<i class="ri-add-line"></i> Add Widget';
        addWidgetBtn.setAttribute('data-action', 'add-widget');
        addWidgetBtn.title = 'Add Widget to Section';
        
        // Edit Button
        const editBtn = document.createElement('button');
        editBtn.className = 'section-toolbar-btn btn-secondary';
        editBtn.innerHTML = '<i class="ri-pencil-fill"></i> Edit';
        editBtn.setAttribute('data-action', 'edit');
        editBtn.title = 'Edit Section Settings';
        
        // Delete Button
        const deleteBtn = document.createElement('button');
        deleteBtn.className = 'section-toolbar-btn btn-danger';
        deleteBtn.innerHTML = '<i class="ri-delete-bin-fill"></i> Delete';
        deleteBtn.setAttribute('data-action', 'delete');
        deleteBtn.title = 'Delete Section';
        
        // Add buttons to container
        toolbarButtons.appendChild(addWidgetBtn);
        toolbarButtons.appendChild(editBtn);
        toolbarButtons.appendChild(deleteBtn);
        
        // Add click handlers
        toolbarButtons.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const button = e.target.closest('.section-toolbar-btn');
            if (button) {
                const action = button.getAttribute('data-action');
                const sectionName = section.getAttribute('data-section-name') || 'Section';
                
                console.log(`🔧 Section toolbar action: ${action} on section "${sectionName}" (ID: ${sectionId})`);
                
                // Call the existing toolbar action handler
                handleToolbarAction(button, section);
            }
        });
        
        // Append to section
        section.appendChild(toolbarButtons);
        
        console.log(`✅ Created toolbar buttons for section ${sectionId}`);
    }
    
    function deselectAll() {
        document.querySelectorAll('.preview-highlighted').forEach(el => {
            el.classList.remove('preview-highlighted');
            el.style.boxShadow = '';
            // Remove toolbar buttons when deselecting
            const existingButtons = el.querySelector('.section-toolbar-buttons');
            if (existingButtons) {
                existingButtons.remove();
            }
        });
    }
    
    function navigateWidgets(currentWidget, direction) {
        const widgets = Array.from(document.querySelectorAll('[data-preview-widget]'));
        const currentIndex = widgets.indexOf(currentWidget);
        
        let nextIndex;
        
        switch (direction) {
            case 'ArrowUp':
            case 'ArrowLeft':
                nextIndex = currentIndex > 0 ? currentIndex - 1 : widgets.length - 1;
                break;
            case 'ArrowDown':
            case 'ArrowRight':
                nextIndex = currentIndex < widgets.length - 1 ? currentIndex + 1 : 0;
                break;
        }
        
        if (nextIndex !== undefined && widgets[nextIndex]) {
            const widget = widgets[nextIndex];
            selectWidget(
                widget.dataset.previewWidget,
                widget.dataset.widgetId,
                widget.dataset.sectionId,
                widget.dataset.widgetName
            );
            widget.focus();
        }
    }
    
    function handleDeviceChange(device, settings) {
        // Update body class for device-specific styles
        document.body.className = document.body.className.replace(/device-\w+/g, '');
        document.body.classList.add(`device-${device}`);
        
        console.log(`📱 Device changed to: ${device}`);
    }
    
    function scrollToWidget(instanceId) {
        const widget = document.querySelector(`[data-preview-widget="${instanceId}"]`);
        if (widget) {
            widget.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'center',
                inline: 'center'
            });
        }
    }
    
    function updateWidgetState(instanceId, state) {
        const widget = document.querySelector(`[data-preview-widget="${instanceId}"]`);
        if (!widget) return;
        
        // Remove existing state classes
        widget.classList.remove('widget-loading', 'widget-updating', 'widget-updated', 'widget-error');
        
        // Add new state class
        if (state) {
            widget.classList.add(`widget-${state}`);
            
            // Auto-remove temporary states
            if (['updated', 'error'].includes(state)) {
                setTimeout(() => {
                    widget.classList.remove(`widget-${state}`);
                }, 3000);
            }
        }
    }
    
    // Expose helper functions globally for parent window access
    window.previewHelpers = {
        highlightWidget,
        highlightSection,
        deselectAll,
        selectWidget,
        selectSection,
        scrollToWidget,
        updateWidgetState,
        
        // Utility functions
        getSelectedWidget() {
            const highlighted = document.querySelector('.preview-highlighted[data-preview-widget]');
            return highlighted ? {
                instanceId: highlighted.dataset.previewWidget,
                widgetId: highlighted.dataset.widgetId,
                sectionId: highlighted.dataset.sectionId,
                widgetName: highlighted.dataset.widgetName
            } : null;
        },
        
        getSelectedSection() {
            const highlighted = document.querySelector('.preview-highlighted[data-preview-section]');
            return highlighted ? {
                sectionId: highlighted.dataset.previewSection,
                sectionName: highlighted.dataset.sectionName
            } : null;
        },
        
        getAllWidgets() {
            return Array.from(document.querySelectorAll('[data-preview-widget]'))
                .map(widget => ({
                    instanceId: widget.dataset.previewWidget,
                    widgetId: widget.dataset.widgetId,
                    sectionId: widget.dataset.sectionId,
                    widgetName: widget.dataset.widgetName
                }));
        },
        
        getAllSections() {
            return Array.from(document.querySelectorAll('[data-preview-section]'))
                .map(section => ({
                    sectionId: section.dataset.previewSection,
                    sectionName: section.dataset.sectionName
                }));
        },
        
        getWidgetInfo(instanceId) {
            const widget = document.querySelector(`[data-preview-widget="${instanceId}"]`);
            if (widget) {
                const rect = widget.getBoundingClientRect();
                return {
                    instanceId: instanceId,
                    widgetId: widget.dataset.widgetId,
                    sectionId: widget.dataset.sectionId,
                    widgetName: widget.dataset.widgetName,
                    position: { x: rect.left, y: rect.top },
                    size: { width: rect.width, height: rect.height },
                    visible: rect.top >= 0 && rect.bottom <= window.innerHeight
                };
            }
            return null;
        },
        
        getSectionInfo(sectionId) {
            const section = document.querySelector(`[data-preview-section="${sectionId}"]`);
            if (section) {
                const rect = section.getBoundingClientRect();
                return {
                    sectionId: sectionId,
                    sectionName: section.dataset.sectionName,
                    position: { x: rect.left, y: rect.top },
                    size: { width: rect.width, height: rect.height },
                    visible: rect.top >= 0 && rect.bottom <= window.innerHeight
                };
            }
            return null;
        }
    };
    
    // Toolbar Action Handlers
    function handleToolbarAction(actionElement, targetElement) {
        const action = actionElement.dataset.action;
        const elementType = targetElement.dataset.previewWidget ? 'widget' : 'section';
        const elementId = targetElement.dataset.previewWidget || targetElement.dataset.previewSection;
        const elementName = targetElement.dataset.widgetName || targetElement.dataset.sectionName;
        
        console.log(`🔧 Toolbar action: ${action} on ${elementType} "${elementName}" (ID: ${elementId})`);
        
        switch (action) {
            case 'edit':
                // Send toolbar action message to parent for both widgets and sections
                parent.postMessage({
                    type: 'toolbar-action',
                    data: { 
                        action: 'edit',
                        elementType: elementType,
                        elementId: elementId,
                        elementName: elementName
                    }
                }, '*');
                
                console.log(`✏️ Edit ${elementType}: ${elementName} (ID: ${elementId}) - message sent to parent`);
                break;
                
            case 'copy':
                handleCopyElement(elementType, elementId, elementName);
                break;
                
            case 'delete':
                handleDeleteElement(elementType, elementId, elementName);
                break;
                
            case 'move-up':
            case 'move-down':
                handleMoveElement(elementType, elementId, action);
                break;
                
            case 'add-widget':
                if (elementType === 'section') {
                    handleAddWidget(elementId);
                }
                break;
                
            default:
                console.warn(`Unknown toolbar action: ${action}`);
        }
    }
    
    function handleCopyElement(type, id, name) {
        parent.postMessage({
            type: 'toolbar-action',
            data: { 
                action: 'copy',
                elementType: type,
                elementId: id,
                elementName: name
            }
        }, '*');
        
        console.log(`📋 Copy ${type}: ${name}`);
    }
    
    function handleDeleteElement(type, id, name) {
        if (confirm(`Are you sure you want to delete "${name}"?`)) {
            parent.postMessage({
                type: 'toolbar-action',
                data: { 
                    action: 'delete',
                    elementType: type,
                    elementId: id,
                    elementName: name
                }
            }, '*');
            
            console.log(`🗑️ Delete ${type}: ${name}`);
        }
    }
    
    function handleMoveElement(type, id, direction) {
        parent.postMessage({
            type: 'toolbar-action',
            data: { 
                action: direction,
                elementType: type,
                elementId: id
            }
        }, '*');
        
        console.log(`📈 Move ${type} ${direction}: ${id}`);
    }
    
    function handleAddWidget(sectionId) {
        parent.postMessage({
            type: 'toolbar-action',
            data: { 
                action: 'add-widget',
                elementType: 'section',
                elementId: sectionId
            }
        }, '*');
        
        console.log(`➕ Add widget to section: ${sectionId}`);
    }
    
})();