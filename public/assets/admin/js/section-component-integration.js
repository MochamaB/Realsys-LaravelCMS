/**
 * Section Component Integration for GrapesJS Page Designer
 * Phase 2.3 Integration Script
 * 
 * Integrates the Section Component Factory with the existing page designer
 * and connects it to the Widget Component Factory for complete functionality.
 */

(function() {
    'use strict';

    // Wait for widget components to be ready
    window.addEventListener('widgetComponentsReady', function(event) {
        const { factory: widgetFactory, editor } = event.detail;
        
        // Initialize section components after widget components are ready
        setTimeout(() => initializeSectionComponents(editor, widgetFactory), 500);
    });

    /**
     * Initialize section components integration
     */
    async function initializeSectionComponents(editor, widgetFactory) {
        console.log('🏗️ Initializing Section Component Integration...');

        try {
            // Initialize Section Component Factory
            const sectionFactory = new SectionComponentFactory(editor, widgetFactory);
            
            // Store factory globally for access
            window.sectionComponentFactory = sectionFactory;
            
            // Set up additional integrations
            setupSectionCommands(editor, sectionFactory);
            setupSectionPanels(editor, sectionFactory);
            setupSectionStyles(editor);
            setupSectionKeyboardShortcuts(editor, sectionFactory);
            setupDropZoneHandlers(editor);
            
            console.log('✅ Section Component Integration initialized successfully');
            
            // Trigger custom event for other scripts
            window.dispatchEvent(new CustomEvent('sectionComponentsReady', {
                detail: { 
                    sectionFactory: sectionFactory, 
                    widgetFactory: widgetFactory,
                    editor: editor 
                }
            }));
            
        } catch (error) {
            console.error('❌ Failed to initialize Section Component Integration:', error);
            showSectionInitializationError(error);
        }
    }

    /**
     * Set up section-related commands
     */
    function setupSectionCommands(editor, sectionFactory) {
        const commands = editor.Commands;

        // Command to add new section
        commands.add('add-section', {
            run(editor, sender, options = {}) {
                const sectionType = options.sectionType || 'full-width';
                const typeConfig = sectionFactory.getSectionType(sectionType);
                
                if (!typeConfig) {
                    console.error('Unknown section type:', sectionType);
                    return;
                }

                console.log('➕ Adding new section:', typeConfig.name);
                
                // Add section to canvas
                const wrapper = editor.Components.getWrapper();
                const newSection = wrapper.components().add({
                    type: `section-${sectionType}`,
                    attributes: {
                        'data-section-type': sectionType
                    }
                });

                // Select the new section
                editor.select(newSection);
                
                // Show success notification
                showSectionNotification(`${typeConfig.name} section added`, 'success');
            }
        });

        // Command to duplicate section
        commands.add('duplicate-section', {
            run(editor) {
                const selected = editor.getSelected();
                if (!selected || !selected.get('type')?.includes('section')) {
                    showSectionNotification('Please select a section to duplicate', 'warning');
                    return;
                }

                console.log('📋 Duplicating section');
                
                // Clone the section
                const cloned = selected.clone();
                const parent = selected.parent();
                const index = parent.components().indexOf(selected);
                
                // Insert after the original
                parent.components().add(cloned, { at: index + 1 });
                
                // Select the cloned section
                editor.select(cloned);
                
                showSectionNotification('Section duplicated successfully', 'success');
            }
        });

        // Command to move section up
        commands.add('move-section-up', {
            run(editor) {
                const selected = editor.getSelected();
                if (!selected || !selected.get('type')?.includes('section')) {
                    showSectionNotification('Please select a section to move', 'warning');
                    return;
                }

                const parent = selected.parent();
                const components = parent.components();
                const index = components.indexOf(selected);
                
                if (index > 0) {
                    components.remove(selected);
                    components.add(selected, { at: index - 1 });
                    editor.select(selected);
                    showSectionNotification('Section moved up', 'success');
                } else {
                    showSectionNotification('Section is already at the top', 'info');
                }
            }
        });

        // Command to move section down
        commands.add('move-section-down', {
            run(editor) {
                const selected = editor.getSelected();
                if (!selected || !selected.get('type')?.includes('section')) {
                    showSectionNotification('Please select a section to move', 'warning');
                    return;
                }

                const parent = selected.parent();
                const components = parent.components();
                const index = components.indexOf(selected);
                
                if (index < components.length - 1) {
                    components.remove(selected);
                    components.add(selected, { at: index + 1 });
                    editor.select(selected);
                    showSectionNotification('Section moved down', 'success');
                } else {
                    showSectionNotification('Section is already at the bottom', 'info');
                }
            }
        });

        // Command to clear section
        commands.add('clear-section', {
            run(editor) {
                const selected = editor.getSelected();
                if (!selected || !selected.get('type')?.includes('section')) {
                    showSectionNotification('Please select a section to clear', 'warning');
                    return;
                }

                if (confirm('Are you sure you want to remove all widgets from this section?')) {
                    // Find all widget components in the section
                    const widgets = selected.find('[data-gjs-type="widget"]');
                    
                    widgets.forEach(widget => {
                        widget.remove();
                    });
                    
                    showSectionNotification('Section cleared successfully', 'success');
                }
            }
        });

        // Command to refresh section schemas
        commands.add('refresh-section-schemas', {
            async run(editor) {
                console.log('🔄 Refreshing section schemas...');
                
                try {
                    await sectionFactory.refreshSectionSchemas();
                    showSectionNotification('Section schemas refreshed successfully', 'success');
                } catch (error) {
                    console.error('Failed to refresh section schemas:', error);
                    showSectionNotification('Failed to refresh section schemas', 'error');
                }
            }
        });
    }

    /**
     * Set up section-related panels
     */
    function setupSectionPanels(editor, sectionFactory) {
        const panels = editor.Panels;

        // Add section management buttons to the toolbar
        panels.addButton('options', {
            id: 'add-section-dropdown',
            className: 'fa fa-plus-square',
            command: 'show-add-section-menu',
            attributes: { title: 'Add Section' }
        });

        panels.addButton('options', {
            id: 'section-tools',
            className: 'fa fa-object-group',
            command: 'show-section-tools',
            attributes: { title: 'Section Tools' }
        });

        // Command to show add section menu
        editor.Commands.add('show-add-section-menu', {
            run(editor) {
                showAddSectionMenu(editor, sectionFactory);
            }
        });

        // Command to show section tools
        editor.Commands.add('show-section-tools', {
            run(editor) {
                showSectionToolsMenu(editor);
            }
        });
    }

    /**
     * Set up section-specific styles
     */
    function setupSectionStyles(editor) {
        // Add section-specific CSS to the canvas
        const canvasDoc = editor.Canvas.getDocument();
        const styleElement = canvasDoc.createElement('style');
        styleElement.id = 'section-component-styles';
        styleElement.textContent = `
            /* Section Component Styles */
            .gjs-section-component {
                position: relative;
                margin: 20px 0;
                border: 2px dashed transparent;
                transition: border-color 0.2s ease;
                min-height: 100px;
            }

            .gjs-section-component:hover {
                border-color: #405189;
            }

            .gjs-section-component.gjs-selected {
                border-color: #405189;
                box-shadow: 0 0 0 1px #405189;
            }

            /* Section Header */
            .gjs-section-header {
                background: #405189;
                color: white;
                padding: 8px 15px;
                margin-bottom: 10px;
                border-radius: 4px;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .section-header-content {
                display: flex;
                justify-content: space-between;
                align-items: center;
                width: 100%;
            }

            .section-title {
                margin: 0;
                font-size: 14px;
                font-weight: 600;
            }

            .section-controls {
                display: flex;
                gap: 5px;
            }

            .section-controls .btn {
                padding: 2px 6px;
                font-size: 12px;
                line-height: 1;
            }

            /* Section Container */
            .gjs-section-container {
                padding: 0;
            }

            .gjs-section-row {
                margin: 0;
            }

            .gjs-section-column {
                padding: 15px;
                min-height: 100px;
                border: 1px dashed #dee2e6;
                margin-bottom: 10px;
                position: relative;
                transition: all 0.2s ease;
            }

            .gjs-section-column:hover {
                border-color: #405189;
                background-color: rgba(64, 81, 137, 0.05);
            }

            .gjs-section-column.drag-over {
                border-color: #28a745;
                background-color: rgba(40, 167, 69, 0.1);
            }

            /* Drop Zone Placeholder */
            .gjs-drop-zone-placeholder {
                display: flex;
                align-items: center;
                justify-content: center;
                height: 80px;
                border: 2px dashed #dee2e6;
                border-radius: 8px;
                color: #6c757d;
                background: #f8f9fa;
                transition: all 0.2s ease;
            }

            .gjs-drop-zone-placeholder:hover {
                border-color: #405189;
                color: #405189;
                background: rgba(64, 81, 137, 0.05);
            }

            .drop-zone-content {
                text-align: center;
            }

            .drop-zone-icon {
                font-size: 24px;
                display: block;
                margin-bottom: 5px;
            }

            .drop-zone-text {
                margin: 0;
                font-size: 14px;
                font-weight: 500;
            }

            /* Section Loading State */
            .section-loading-state {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: 40px 20px;
                background: #f8f9fa;
                border: 2px dashed #dee2e6;
                border-radius: 8px;
                color: #6c757d;
            }

            .section-loading-state .loading-spinner {
                width: 30px;
                height: 30px;
                border: 3px solid #dee2e6;
                border-top: 3px solid #405189;
                border-radius: 50%;
                animation: spin 1s linear infinite;
                margin-bottom: 15px;
            }

            /* Section Error State */
            .section-error-state {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                padding: 30px 20px;
                background: #fff5f5;
                border: 2px dashed #f56565;
                border-radius: 8px;
                color: #c53030;
                text-align: center;
            }

            .section-error-state .error-icon {
                font-size: 24px;
                margin-bottom: 10px;
            }

            .section-error-state h4 {
                margin: 0 0 10px 0;
                color: #c53030;
            }

            .section-error-state .error-message {
                margin: 0 0 15px 0;
                font-size: 14px;
                opacity: 0.8;
            }

            .section-error-state .retry-btn {
                background: #c53030;
                color: white;
                border: none;
                padding: 8px 16px;
                border-radius: 4px;
                cursor: pointer;
                font-size: 12px;
            }

            .section-error-state .retry-btn:hover {
                background: #9c2626;
            }

            /* Responsive adjustments */
            @media (max-width: 768px) {
                .gjs-section-column {
                    margin-bottom: 5px;
                    padding: 10px;
                }
            }
        `;
        
        canvasDoc.head.appendChild(styleElement);
    }

    /**
     * Set up keyboard shortcuts for sections
     */
    function setupSectionKeyboardShortcuts(editor, sectionFactory) {
        // Add section: Ctrl+Shift+A
        editor.Keymaps.add('core', 'ctrl+shift+a', 'add-section');
        
        // Duplicate section: Ctrl+D
        editor.Keymaps.add('core', 'ctrl+d', 'duplicate-section');
        
        // Move section up: Ctrl+Up
        editor.Keymaps.add('core', 'ctrl+up', 'move-section-up');
        
        // Move section down: Ctrl+Down
        editor.Keymaps.add('core', 'ctrl+down', 'move-section-down');
        
        // Clear section: Ctrl+Shift+X
        editor.Keymaps.add('core', 'ctrl+shift+x', 'clear-section');
    }

    /**
     * Set up drop zone handlers
     */
    function setupDropZoneHandlers(editor) {
        // Handle drop zone interactions
        editor.on('component:drag:start', (component) => {
            if (component.get('type')?.includes('widget')) {
                highlightDropZones(editor, true);
            }
        });

        editor.on('component:drag:end', (component) => {
            if (component.get('type')?.includes('widget')) {
                highlightDropZones(editor, false);
            }
        });
    }

    /**
     * Highlight drop zones during drag operations
     */
    function highlightDropZones(editor, highlight) {
        const canvasDoc = editor.Canvas.getDocument();
        const dropZones = canvasDoc.querySelectorAll('.gjs-section-column');
        
        dropZones.forEach(zone => {
            if (highlight) {
                zone.classList.add('drop-zone-active');
            } else {
                zone.classList.remove('drop-zone-active');
            }
        });
    }

    /**
     * Show add section menu
     */
    function showAddSectionMenu(editor, sectionFactory) {
        const sectionTypes = Array.from(sectionFactory.sectionTypes.entries());
        
        const menuItems = sectionTypes.map(([type, config]) => `
            <div class="section-menu-item" data-section-type="${type}">
                <i class="${config.icon}"></i>
                <div class="section-menu-info">
                    <strong>${config.name}</strong>
                    <small>${config.description}</small>
                </div>
            </div>
        `).join('');

        const menuHTML = `
            <div class="add-section-menu">
                <h6>Add Section</h6>
                <div class="section-menu-items">
                    ${menuItems}
                </div>
            </div>
        `;

        // Show modal with section types
        editor.Modal.open({
            title: 'Add Section',
            content: menuHTML
        });

        // Handle section type selection
        setTimeout(() => {
            const menuItemElements = document.querySelectorAll('.section-menu-item');
            menuItemElements.forEach(item => {
                item.addEventListener('click', () => {
                    const sectionType = item.dataset.sectionType;
                    editor.runCommand('add-section', { sectionType });
                    editor.Modal.close();
                });
            });
        }, 100);
    }

    /**
     * Show section tools menu
     */
    function showSectionToolsMenu(editor) {
        const selected = editor.getSelected();
        const isSection = selected && selected.get('type')?.includes('section');
        
        const toolsHTML = `
            <div class="section-tools-menu">
                <h6>Section Tools</h6>
                <div class="section-tool-buttons">
                    <button class="btn btn-sm btn-outline-primary" ${!isSection ? 'disabled' : ''} 
                            onclick="window.editor.runCommand('duplicate-section')">
                        📋 Duplicate Section
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" ${!isSection ? 'disabled' : ''} 
                            onclick="window.editor.runCommand('move-section-up')">
                        ⬆️ Move Up
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" ${!isSection ? 'disabled' : ''} 
                            onclick="window.editor.runCommand('move-section-down')">
                        ⬇️ Move Down
                    </button>
                    <button class="btn btn-sm btn-outline-warning" ${!isSection ? 'disabled' : ''} 
                            onclick="window.editor.runCommand('clear-section')">
                        🗑️ Clear Section
                    </button>
                    <button class="btn btn-sm btn-outline-info" 
                            onclick="window.editor.runCommand('refresh-section-schemas')">
                        🔄 Refresh Schemas
                    </button>
                </div>
                ${!isSection ? '<p class="text-muted"><small>Select a section to enable tools</small></p>' : ''}
            </div>
        `;

        editor.Modal.open({
            title: 'Section Tools',
            content: toolsHTML
        });
    }

    /**
     * Show section notification
     */
    function showSectionNotification(message, type = 'info') {
        // Use the existing notification system from widget integration
        if (window.WidgetComponentIntegration && window.WidgetComponentIntegration.showNotification) {
            window.WidgetComponentIntegration.showNotification(message, type);
        } else {
            // Fallback notification
            console.log(`[${type.toUpperCase()}] ${message}`);
        }
    }

    /**
     * Show section initialization error
     */
    function showSectionInitializationError(error) {
        const errorContainer = document.createElement('div');
        errorContainer.innerHTML = `
            <div style="
                position: fixed;
                bottom: 20px;
                right: 20px;
                background: #fff5f5;
                color: #c53030;
                padding: 20px;
                border-radius: 8px;
                border: 2px solid #f56565;
                box-shadow: 0 4px 20px rgba(0,0,0,0.1);
                z-index: 10000;
                max-width: 400px;
            ">
                <h6 style="margin: 0 0 10px 0; color: #c53030;">
                    ⚠️ Section System Warning
                </h6>
                <p style="margin: 0 0 10px 0; font-size: 14px;">
                    Section components could not be fully initialized.
                </p>
                <small style="opacity: 0.8;">
                    ${error.message}
                </small>
            </div>
        `;
        
        document.body.appendChild(errorContainer);
        
        // Remove after 10 seconds
        setTimeout(() => {
            if (errorContainer.parentNode) {
                errorContainer.parentNode.removeChild(errorContainer);
            }
        }, 10000);
    }

    // Add styles for section menus
    const menuStyles = document.createElement('style');
    menuStyles.textContent = `
        .add-section-menu, .section-tools-menu {
            padding: 20px;
        }

        .add-section-menu h6, .section-tools-menu h6 {
            margin-bottom: 15px;
            color: #405189;
        }

        .section-menu-items {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .section-menu-item {
            display: flex;
            align-items: center;
            padding: 12px;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .section-menu-item:hover {
            border-color: #405189;
            background: rgba(64, 81, 137, 0.05);
        }

        .section-menu-item i {
            font-size: 20px;
            margin-right: 12px;
            color: #405189;
        }

        .section-menu-info strong {
            display: block;
            margin-bottom: 2px;
            color: #212529;
        }

        .section-menu-info small {
            color: #6c757d;
            font-size: 12px;
        }

        .section-tool-buttons {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .section-tool-buttons .btn {
            text-align: left;
            padding: 8px 12px;
        }
    `;
    
    document.head.appendChild(menuStyles);

    // Export functions for external use
    window.SectionComponentIntegration = {
        showSectionNotification,
        highlightDropZones
    };

})(); 