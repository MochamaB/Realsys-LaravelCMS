/**
 * PAGE BUILDER PREVIEW HELPER
 * ============================
 * 
 * GENERAL PURPOSE:
 * Manages preview interactions for the Page Builder system specifically.
 * Unlike live-designer/preview-helpers.js, this version is designed to work
 * with the Page Builder's modal system without interference.
 * 
 * KEY DIFFERENCES FROM LIVE-DESIGNER VERSION:
 * • Does NOT use preventDefault() on general clicks (modal-safe)
 * • Only prevents default for specific widget/section selection actions
 * • Properly isolates iframe vs parent window events
 * • Compatible with Bootstrap modals on parent page
 * • Designed for Page Builder's complex interface needs
 * 
 * FUNCTIONS/METHODS:
 * • initializePageBuilderPreview() - **UNIQUE** - Initialize Page Builder specific preview
 * • setupModalSafeWidgetInteractions() - **UNIQUE** - Widget selection without modal interference
 * • setupModalSafeSectionInteractions() - **UNIQUE** - Section selection without modal interference
 * • setupPageBuilderMessageListener() - **UNIQUE** - Parent-iframe communication for Page Builder
 * • selectWidgetSafely() - **UNIQUE** - Widget selection that respects modal triggers
 * • selectSectionSafely() - **UNIQUE** - Section selection that respects modal triggers
 * • isModalTrigger() - **UNIQUE** - Detect if click target is a modal trigger
 * • sendToPageBuilder() - **UNIQUE** - Send messages to Page Builder parent window
 */

(function() {
    'use strict';
    
    // Check if we're in an iframe (preview mode)
    const isInIframe = window !== window.parent;
    const isPageBuilderPreview = isInIframe && window.location.href.includes('/page-builder/');
    
    console.log('🎨 Page Builder Preview Helper Loading...', {
        isInIframe,
        isPageBuilderPreview,
        url: window.location.href
    });
    
    // Only run in Page Builder preview iframes
    if (isPageBuilderPreview) {
        // Initialize when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                initializePageBuilderPreview();
            });
        } else {
            initializePageBuilderPreview();
        }
    } else {
        console.log('🚫 Page Builder Preview Helper: Not in Page Builder iframe, skipping initialization');
    }
    
    function initializePageBuilderPreview() {
        console.log('🎨 Initializing Page Builder Preview...');
        
        // Add preview mode indicator to body
        if (document.body) {
            document.body.setAttribute('data-preview-mode', 'pagebuilder');
            document.body.classList.add('pagebuilder-preview');
        }
        
        // Map page structure to DOM elements
        if (window.previewPageStructure) {
            mapStructureToDOM();
        } else {
            console.warn('⚠️ No page structure data found for Page Builder preview');
        }
        
        // Setup modal-safe interactions
        setupModalSafeWidgetInteractions();
        setupModalSafeSectionInteractions();
        setupPageBuilderMessageListener();
        
        console.log('✅ Page Builder Preview initialized successfully');
    }
    
    function mapStructureToDOM() {
        console.log('🗺️ Mapping Page Builder structure to DOM...');
        
        try {
            const { sections, widgets } = window.previewPageStructure;
            
            // Map sections
            if (sections) {
                Object.entries(sections).forEach(([sectionId, sectionData]) => {
                    const sectionElement = document.querySelector(`[data-section-id="${sectionId}"]`);
                    if (sectionElement) {
                        sectionElement.setAttribute('data-pagebuilder-section', sectionId);
                        sectionElement.setAttribute('data-section-name', sectionData.name || `Section ${sectionId}`);
                        console.log('✅ Mapped section:', sectionId, sectionData.name);
                    }
                });
            }
            
            // Map widgets
            if (widgets) {
                Object.entries(widgets).forEach(([widgetId, widgetData]) => {
                    const widgetElement = document.querySelector(`[data-page-section-widget-id="${widgetId}"]`);
                    if (widgetElement) {
                        widgetElement.setAttribute('data-pagebuilder-widget', widgetId);
                        widgetElement.setAttribute('data-widget-id', widgetData.widget_id || widgetId);
                        widgetElement.setAttribute('data-widget-name', widgetData.name || 'Widget');
                        console.log('✅ Mapped widget:', widgetId, widgetData.name);
                    }
                });
            }
            
        } catch (error) {
            console.error('❌ Error mapping Page Builder structure:', error);
        }
    }
    
    function setupModalSafeWidgetInteractions() {
        console.log('🎯 Setting up modal-safe widget interactions...');
        
        const widgets = document.querySelectorAll('[data-pagebuilder-widget]');
        
        widgets.forEach(widget => {
            // Make widget visually interactive
            widget.style.cursor = 'pointer';
            widget.setAttribute('tabindex', '0');
            
            // Add hover effects
            widget.addEventListener('mouseenter', function() {
                this.style.outline = '2px dashed #007bff';
                this.style.outlineOffset = '2px';
            });
            
            widget.addEventListener('mouseleave', function() {
                if (!this.classList.contains('pagebuilder-selected')) {
                    this.style.outline = 'none';
                }
            });
            
            // MODAL-SAFE click handler
            widget.addEventListener('click', function(e) {
                // Check if the click target or its parent is a modal trigger
                if (isModalTrigger(e.target)) {
                    console.log('🔵 Modal trigger detected, allowing default behavior');
                    return; // Let the modal trigger work normally
                }
                
                // Check if clicking on interactive elements that should work normally
                if (isInteractiveElement(e.target)) {
                    console.log('🔗 Interactive element detected, allowing default behavior');
                    return; // Let links, buttons, forms work normally
                }
                
                // Only prevent default for actual widget selection
                e.preventDefault();
                e.stopPropagation();
                
                selectWidgetSafely(
                    this.dataset.pagebuilderWidget,
                    this.dataset.widgetId,
                    this.dataset.widgetName
                );
            });
            
            // Keyboard handler
            widget.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    selectWidgetSafely(
                        this.dataset.pagebuilderWidget,
                        this.dataset.widgetId,
                        this.dataset.widgetName
                    );
                }
            });
        });
        
        console.log(`✅ Set up interactions for ${widgets.length} widgets`);
    }
    
    function setupModalSafeSectionInteractions() {
        console.log('📄 Setting up modal-safe section interactions...');
        
        const sections = document.querySelectorAll('[data-pagebuilder-section]');
        
        sections.forEach(section => {
            // Add hover effects for sections
            section.addEventListener('mouseenter', function() {
                if (!this.querySelector('[data-pagebuilder-widget]')) {
                    this.style.outline = '2px dashed #28a745';
                    this.style.outlineOffset = '2px';
                }
            });
            
            section.addEventListener('mouseleave', function() {
                if (!this.classList.contains('pagebuilder-selected')) {
                    this.style.outline = 'none';
                }
            });
            
            // MODAL-SAFE click handler for sections
            section.addEventListener('click', function(e) {
                // Only trigger if clicking the section itself, not a child widget
                if (e.target === this || (!e.target.closest('[data-pagebuilder-widget]') && !isModalTrigger(e.target) && !isInteractiveElement(e.target))) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    selectSectionSafely(
                        this.dataset.pagebuilderSection,
                        this.dataset.sectionName
                    );
                }
            });
        });
        
        console.log(`✅ Set up interactions for ${sections.length} sections`);
    }
    
    function setupPageBuilderMessageListener() {
        console.log('📡 Setting up Page Builder message listener...');
        
        window.addEventListener('message', function(event) {
            // Verify message is from Page Builder parent
            if (event.source !== window.parent) return;
            
            const { type, data } = event.data;
            console.log('📨 Page Builder preview received message:', type, data);
            
            switch (type) {
                case 'highlight-widget':
                    highlightWidget(data.widgetId);
                    break;
                    
                case 'highlight-section':
                    highlightSection(data.sectionId);
                    break;
                    
                case 'clear-highlights':
                    clearAllHighlights();
                    break;
                    
                case 'refresh-preview':
                    location.reload();
                    break;
                    
                default:
                    console.log('ℹ️ Unknown Page Builder message type:', type);
            }
        });
    }
    
    function selectWidgetSafely(pageBuilderWidgetId, originalWidgetId, widgetName) {
        console.log('🎯 Page Builder widget selected:', {
            pageBuilderWidgetId,
            originalWidgetId,
            widgetName
        });
        
        // Clear previous selections
        clearAllHighlights();
        
        // Highlight selected widget
        const widget = document.querySelector(`[data-pagebuilder-widget="${pageBuilderWidgetId}"]`);
        if (widget) {
            widget.classList.add('pagebuilder-selected');
            widget.style.outline = '3px solid #007bff';
            
            // Create toolbar buttons for the widget
            createWidgetToolbarButtons(widget, pageBuilderWidgetId, widgetName);
            
            // Scroll into view
            widget.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'center',
                inline: 'center'
            });
        }
        
        // Send selection to Page Builder parent
        sendToPageBuilder('widget-selected', {
            pageBuilderWidgetId,
            originalWidgetId,
            widgetName,
            elementPosition: widget ? getElementPosition(widget) : null
        });
    }
    
    function selectSectionSafely(sectionId, sectionName) {
        console.log('📄 Page Builder section selected:', {
            sectionId,
            sectionName
        });
        
        // Clear previous selections
        clearAllHighlights();
        
        // Highlight selected section
        const section = document.querySelector(`[data-pagebuilder-section="${sectionId}"]`);
        if (section) {
            section.classList.add('pagebuilder-selected');
            section.style.outline = '3px solid #28a745';
            
            // Create toolbar buttons for the section
            createSectionToolbarButtons(section, sectionId, sectionName);
            
            // Scroll into view
            section.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'center',
                inline: 'center'
            });
        }
        
        // Send selection to Page Builder parent
        sendToPageBuilder('section-selected', {
            sectionId,
            sectionName,
            elementPosition: section ? getElementPosition(section) : null
        });
    }
    
    function isModalTrigger(element) {
        // Check if element or its parents have modal trigger attributes
        let current = element;
        while (current && current !== document.body) {
            if (current.getAttribute && (
                current.getAttribute('data-bs-toggle') === 'modal' ||
                current.getAttribute('data-toggle') === 'modal' ||
                current.hasAttribute('data-bs-target') ||
                current.hasAttribute('data-target')
            )) {
                return true;
            }
            current = current.parentElement;
        }
        return false;
    }
    
    function isInteractiveElement(element) {
        // Check for interactive elements that should work normally
        const interactiveTags = ['A', 'BUTTON', 'INPUT', 'SELECT', 'TEXTAREA', 'FORM'];
        let current = element;
        
        while (current && current !== document.body) {
            if (interactiveTags.includes(current.tagName) || 
                current.hasAttribute('onclick') ||
                current.hasAttribute('href') ||
                current.getAttribute('role') === 'button') {
                return true;
            }
            current = current.parentElement;
        }
        return false;
    }
    
    function sendToPageBuilder(type, data) {
        if (window.parent !== window) {
            window.parent.postMessage({
                type: type,
                data: data,
                source: 'pagebuilder-preview'
            }, '*');
        }
    }
    
    function highlightWidget(widgetId) {
        const widget = document.querySelector(`[data-pagebuilder-widget="${widgetId}"]`);
        if (widget) {
            clearAllHighlights();
            widget.classList.add('pagebuilder-highlighted');
            widget.style.outline = '2px dashed #ffc107';
        }
    }
    
    function highlightSection(sectionId) {
        const section = document.querySelector(`[data-pagebuilder-section="${sectionId}"]`);
        if (section) {
            clearAllHighlights();
            section.classList.add('pagebuilder-highlighted');
            section.style.outline = '2px dashed #17a2b8';
        }
    }
    
    function createSectionToolbarButtons(section, sectionId, sectionName) {
        // Remove any existing toolbar
        const existingToolbar = section.querySelector('.pagebuilder-toolbar-buttons');
        if (existingToolbar) {
            existingToolbar.remove();
        }
        
        // Create toolbar buttons container
        const toolbarButtons = document.createElement('div');
        toolbarButtons.className = 'pagebuilder-toolbar-buttons';
        
        // Add Widget Button (Primary action)
        const addWidgetBtn = document.createElement('button');
        addWidgetBtn.className = 'pagebuilder-toolbar-btn btn-primary';
        addWidgetBtn.innerHTML = '<i class="ri-add-line"></i> Add Widget';
        addWidgetBtn.setAttribute('data-action', 'add-widget');
        addWidgetBtn.title = 'Add Widget to Section';
        
        // Edit Button
        const editBtn = document.createElement('button');
        editBtn.className = 'pagebuilder-toolbar-btn btn-success';
        editBtn.innerHTML = '<i class="ri-pencil-fill"></i> Edit';
        editBtn.setAttribute('data-action', 'edit');
        editBtn.title = 'Edit Section Settings';
        
        // Delete Button
        const deleteBtn = document.createElement('button');
        deleteBtn.className = 'pagebuilder-toolbar-btn btn-danger';
        deleteBtn.innerHTML = '<i class="ri-delete-bin-fill"></i> Delete';
        deleteBtn.setAttribute('data-action', 'delete');
        deleteBtn.title = 'Delete Section';
        
        // Add buttons to container
        toolbarButtons.appendChild(addWidgetBtn);
        toolbarButtons.appendChild(editBtn);
        toolbarButtons.appendChild(deleteBtn);
        
        // Add click handlers with modal safety
        toolbarButtons.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const button = e.target.closest('.pagebuilder-toolbar-btn');
            if (button) {
                const action = button.getAttribute('data-action');
                
                console.log(`🔧 Page Builder section toolbar action: ${action} on section "${sectionName}" (ID: ${sectionId})`);
                
                handleToolbarAction(action, 'section', sectionId, sectionName, button);
            }
        });
        
        // Append to section with proper positioning
        section.style.position = 'relative';
        section.appendChild(toolbarButtons);
        
        console.log(`✅ Created Page Builder toolbar buttons for section ${sectionId}`);
    }
    
    function createWidgetToolbarButtons(widget, pageBuilderWidgetId, widgetName) {
        // Remove any existing toolbar
        const existingToolbar = widget.querySelector('.pagebuilder-toolbar-buttons');
        if (existingToolbar) {
            existingToolbar.remove();
        }
        
        // Create toolbar buttons container
        const toolbarButtons = document.createElement('div');
        toolbarButtons.className = 'pagebuilder-toolbar-buttons widget-toolbar';
        
        // Edit Button
        const editBtn = document.createElement('button');
        editBtn.className = 'pagebuilder-toolbar-btn btn-primary';
        editBtn.innerHTML = '<i class="ri-pencil-fill"></i> Edit';
        editBtn.setAttribute('data-action', 'edit');
        editBtn.title = 'Edit Widget Content';
        
        // Copy Button
        const copyBtn = document.createElement('button');
        copyBtn.className = 'pagebuilder-toolbar-btn btn-info';
        copyBtn.innerHTML = '<i class="ri-file-copy-line"></i> Copy';
        copyBtn.setAttribute('data-action', 'copy');
        copyBtn.title = 'Copy Widget';
        
        // Delete Button
        const deleteBtn = document.createElement('button');
        deleteBtn.className = 'pagebuilder-toolbar-btn btn-danger';
        deleteBtn.innerHTML = '<i class="ri-delete-bin-fill"></i> Delete';
        deleteBtn.setAttribute('data-action', 'delete');
        deleteBtn.title = 'Delete Widget';
        
        // Add buttons to container
        toolbarButtons.appendChild(editBtn);
        toolbarButtons.appendChild(copyBtn);
        toolbarButtons.appendChild(deleteBtn);
        
        // Add click handlers with modal safety
        toolbarButtons.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const button = e.target.closest('.pagebuilder-toolbar-btn');
            if (button) {
                const action = button.getAttribute('data-action');
                
                console.log(`🔧 Page Builder widget toolbar action: ${action} on widget "${widgetName}" (ID: ${pageBuilderWidgetId})`);
                
                handleToolbarAction(action, 'widget', pageBuilderWidgetId, widgetName, button);
            }
        });
        
        // Append to widget with proper positioning
        widget.style.position = 'relative';
        widget.appendChild(toolbarButtons);
        
        console.log(`✅ Created Page Builder toolbar buttons for widget ${pageBuilderWidgetId}`);
    }
    
    function handleToolbarAction(action, elementType, elementId, elementName, buttonElement) {
        console.log(`🔧 Page Builder toolbar action: ${action} on ${elementType} "${elementName}" (ID: ${elementId})`);
        
        switch (action) {
            case 'edit':
                // Send toolbar action message to parent Page Builder
                sendToPageBuilder('toolbar-action', {
                    action: 'edit',
                    elementType: elementType,
                    elementId: elementId,
                    elementName: elementName
                });
                
                console.log(`✏️ Edit ${elementType}: ${elementName} (ID: ${elementId}) - message sent to Page Builder parent`);
                break;
                
            case 'add-widget':
                // Only for sections
                if (elementType === 'section') {
                    sendToPageBuilder('toolbar-action', {
                        action: 'add-widget',
                        elementType: 'section',
                        elementId: elementId,
                        elementName: elementName
                    });
                    
                    console.log(`➕ Add widget to section: ${elementId} (${elementName})`);
                }
                break;
                
            case 'copy':
                sendToPageBuilder('toolbar-action', {
                    action: 'copy',
                    elementType: elementType,
                    elementId: elementId,
                    elementName: elementName
                });
                
                console.log(`📋 Copy ${elementType}: ${elementName}`);
                break;
                
            case 'delete':
                // Add confirmation for delete actions
                if (confirm(`Are you sure you want to delete "${elementName}"?`)) {
                    sendToPageBuilder('toolbar-action', {
                        action: 'delete',
                        elementType: elementType,
                        elementId: elementId,
                        elementName: elementName
                    });
                    
                    console.log(`🗑️ Delete ${elementType}: ${elementName}`);
                }
                break;
                
            default:
                console.warn(`Unknown Page Builder toolbar action: ${action}`);
        }
    }
    
    function clearAllHighlights() {
        // Clear all previous highlights and toolbars
        document.querySelectorAll('.pagebuilder-selected, .pagebuilder-highlighted').forEach(element => {
            element.classList.remove('pagebuilder-selected', 'pagebuilder-highlighted');
            element.style.outline = 'none';
            
            // Remove existing toolbar buttons
            const existingToolbar = element.querySelector('.pagebuilder-toolbar-buttons');
            if (existingToolbar) {
                existingToolbar.remove();
            }
        });
    }
    
    function getElementPosition(element) {
        const rect = element.getBoundingClientRect();
        return {
            top: rect.top + window.scrollY,
            left: rect.left + window.scrollX,
            width: rect.width,
            height: rect.height,
            bottom: rect.bottom + window.scrollY,
            right: rect.right + window.scrollX
        };
    }
    
    // Utility functions for debugging
    window.pageBuilderPreview = {
        selectWidget: selectWidgetSafely,
        selectSection: selectSectionSafely,
        clearHighlights: clearAllHighlights,
        sendMessage: sendToPageBuilder,
        isModalTrigger: isModalTrigger,
        isInteractiveElement: isInteractiveElement
    };
    
})();