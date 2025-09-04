/**
 * Section Preview Module
 * 
 * Handles section-specific interactions, selection, highlighting, drag & drop,
 * and toolbar functionality within the preview iframe environment
 */

(function() {
    'use strict';

    // Section Preview Module
    const SectionPreview = {
        // Initialize section preview functionality
        init: function(sharedState) {
            this.sharedState = sharedState;
            this.setupSectionInteractions();
            console.log('📦 Section preview module initialized');
        },

        // Setup section interactions
        setupSectionInteractions: function() {
            const sections = document.querySelectorAll('[data-preview-section]');
            
            sections.forEach(section => {
                section.addEventListener('click', (e) => {
                    // Only trigger if clicking the section itself, not a child widget
                    if (e.target === section || !e.target.closest('[data-preview-widget]')) {
                        e.preventDefault();
                        e.stopPropagation();
                        this.selectSection(
                            section.dataset.previewSection, // Section ID
                            section.dataset.sectionName // Section name for display
                        );
                    }
                });
            });
            
            console.log(`📦 Setup interactions for ${sections.length} sections`);
        },

        // Select a section
        selectSection: function(sectionId, sectionName) {
            // Clear previous selections
            this.sharedState.deselectAll();
            
            // Highlight selected section
            this.highlightSection(sectionId);
            
            // Notify parent
            parent.postMessage({
                type: 'section-selected',
                data: { 
                    sectionId: sectionId,
                    sectionName: sectionName 
                }
            }, '*');
            
            console.log(`✓ Section selected: ${sectionName} (ID: ${sectionId})`);
        },

        // Highlight a section
        highlightSection: function(sectionId) {
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
                this.createSectionToolbarButtons(section, sectionId);
                
                // Scroll into view with some top offset to account for toolbar
                section.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center',
                    inline: 'center'
                });
            }
        },

        // Create section toolbar buttons
        createSectionToolbarButtons: function(section, sectionId) {
            // Create toolbar buttons container
            const toolbarButtons = document.createElement('div');
            toolbarButtons.className = 'section-toolbar-buttons';
            
            // Drag Handle Button (for visual drag initiation)
            const dragBtn = document.createElement('button');
            dragBtn.className = 'section-toolbar-btn btn-secondary section-drag-handle';
            dragBtn.innerHTML = '<i class="ri-drag-move-2-line"></i>';
            dragBtn.setAttribute('data-action', 'drag-handle');
            dragBtn.title = 'Drag to Reorder Section';
            
            // Add Widget Button (Primary action)
            const addWidgetBtn = document.createElement('button');
            addWidgetBtn.className = 'section-toolbar-btn btn-primary';
            addWidgetBtn.innerHTML = '<i class="ri-add-line"></i> Add Widget';
            addWidgetBtn.setAttribute('data-action', 'add-widget');
            addWidgetBtn.title = 'Add Widget to Section';
            
            // Clone Button
            const cloneBtn = document.createElement('button');
            cloneBtn.className = 'section-toolbar-btn btn-info';
            cloneBtn.innerHTML = '<i class="ri-file-copy-line"></i>';
            cloneBtn.setAttribute('data-action', 'clone');
            cloneBtn.title = 'Clone Section';
            
            // Edit Button
            const editBtn = document.createElement('button');
            editBtn.className = 'section-toolbar-btn btn-primary';
            editBtn.innerHTML = '<i class="ri-pencil-fill"></i> Edit';
            editBtn.setAttribute('data-action', 'edit');
            editBtn.title = 'Edit Section Settings';
            
            // Delete Button
            const deleteBtn = document.createElement('button');
            deleteBtn.className = 'section-toolbar-btn btn-danger';
            deleteBtn.innerHTML = '<i class="ri-delete-bin-fill"></i> Delete';
            deleteBtn.setAttribute('data-action', 'delete');
            deleteBtn.title = 'Delete Section';
            
            // Add buttons to container in logical order
            toolbarButtons.appendChild(dragBtn);
            toolbarButtons.appendChild(addWidgetBtn);
            toolbarButtons.appendChild(cloneBtn);
            toolbarButtons.appendChild(editBtn);
            toolbarButtons.appendChild(deleteBtn);
            
            // Add click handlers
            toolbarButtons.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                
                const button = e.target.closest('.section-toolbar-btn');
                if (button) {
                    const action = button.getAttribute('data-action');
                    const sectionName = section.getAttribute('data-section-name') || 'Section';
                    
                    console.log(`🔧 Section toolbar action: ${action} on section "${sectionName}" (ID: ${sectionId})`);
                    
                    // Call the toolbar action handler
                    this.handleToolbarAction(button, section);
                }
            });
            
            // Append to section
            section.appendChild(toolbarButtons);
            
            // Set up zoom-aware toolbar scaling
            this.updateToolbarZoomScale(section);
            
            console.log(`✅ Created toolbar buttons for section ${sectionId}`);
        },

        // Handle toolbar actions for sections
        handleToolbarAction: function(actionElement, targetElement) {
            const action = actionElement.dataset.action;
            const elementId = targetElement.dataset.previewSection;
            const elementName = targetElement.dataset.sectionName;
            
            console.log(`🔧 Section toolbar action: ${action} on section "${elementName}" (ID: ${elementId})`);
            
            switch (action) {
                case 'drag-handle':
                    this.initiateSectionDrag(elementId, targetElement);
                    break;
                    
                case 'clone':
                    this.handleCloneSection(elementId, elementName);
                    break;
                    
                case 'edit':
                    // Send toolbar action message to parent
                    parent.postMessage({
                        type: 'toolbar-action',
                        data: { 
                            action: 'edit',
                            elementType: 'section',
                            elementId: elementId,
                            elementName: elementName
                        }
                    }, '*');
                    
                    console.log(`✏️ Edit section: ${elementName} (ID: ${elementId}) - message sent to parent`);
                    break;
                    
                case 'delete':
                    this.handleDeleteSection(elementId, elementName);
                    break;
                    
                case 'add-widget':
                    this.handleAddWidget(elementId);
                    break;
                    
                default:
                    console.warn(`Unknown section toolbar action: ${action}`);
            }
        },

        // Handle section delete
        handleDeleteSection: function(id, name) {
            if (confirm(`Are you sure you want to delete "${name}"?`)) {
                parent.postMessage({
                    type: 'toolbar-action',
                    data: { 
                        action: 'delete',
                        elementType: 'section',
                        elementId: id,
                        elementName: name
                    }
                }, '*');
                
                console.log(`🗑️ Delete section: ${name}`);
            }
        },

        // Handle add widget to section
        handleAddWidget: function(sectionId) {
            // Find the section element to get its name
            const sectionElement = document.querySelector(`[data-preview-section="${sectionId}"]`);
            const sectionName = sectionElement ? sectionElement.getAttribute('data-section-name') : null;
            
            parent.postMessage({
                type: 'toolbar-action',
                data: { 
                    action: 'add-widget',
                    elementType: 'section',
                    elementId: sectionId,
                    elementName: sectionName
                }
            }, '*');
            
            console.log(`➕ Add widget to section: ${sectionId} (${sectionName || 'unnamed'})`);
        },

        // Initiate section drag
        initiateSectionDrag: function(sectionId, sectionElement) {
            console.log(`🎯 Initiating drag for section: ${sectionId}`);
            
            // Add visual feedback that drag mode is active
            sectionElement.classList.add('drag-mode-active');
            
            // Show user instruction
            const instruction = document.createElement('div');
            instruction.className = 'drag-instruction';
            instruction.innerHTML = '<i class="ri-information-line"></i> Click and drag the section to reorder';
            instruction.style.cssText = `
                position: absolute;
                top: -40px;
                left: 50%;
                transform: translateX(-50%);
                background: #007bff;
                color: white;
                padding: 5px 10px;
                border-radius: 4px;
                font-size: 12px;
                z-index: 1001;
                white-space: nowrap;
            `;
            sectionElement.appendChild(instruction);
            
            // Remove instruction after 3 seconds
            setTimeout(() => {
                if (instruction.parentNode) {
                    instruction.remove();
                }
                sectionElement.classList.remove('drag-mode-active');
            }, 3000);
            
            // Notify parent about drag mode activation
            parent.postMessage({
                type: 'section-drag-mode',
                data: {
                    sectionId: sectionId,
                    active: true
                }
            }, '*');
        },

        // Handle section cloning
        handleCloneSection: async function(sectionId, sectionName) {
            console.log(`📋 Cloning section: ${sectionId} (${sectionName || 'unnamed'})`);
            
            try {
                // Show loading state
                const sectionElement = document.querySelector(`[data-preview-section="${sectionId}"]`);
                if (sectionElement) {
                    sectionElement.style.opacity = '0.7';
                }
                
                const response = await fetch(`/admin/live-preview/sections/${sectionId}/clone`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    console.log('✅ Section cloned successfully');
                    
                    // Notify parent about successful clone
                    parent.postMessage({
                        type: 'section-cloned',
                        data: {
                            originalSectionId: sectionId,
                            newSectionId: result.newSectionId,
                            sectionName: sectionName
                        }
                    }, '*');
                    
                    // Reload iframe to show cloned section
                    window.location.reload();
                } else {
                    console.error('❌ Failed to clone section:', result.error);
                    alert('Failed to clone section: ' + (result.error || 'Unknown error'));
                }
            } catch (error) {
                console.error('❌ Error cloning section:', error);
                alert('Error cloning section: ' + error.message);
            } finally {
                // Restore opacity
                const sectionElement = document.querySelector(`[data-preview-section="${sectionId}"]`);
                if (sectionElement) {
                    sectionElement.style.opacity = '';
                }
            }
        },

        // Initialize section sorting with SortableJS
        initializeSectionSorting: function() {
            // Find the main container holding all sections
            const pageContainer = document.querySelector('[data-page-id]') || 
                                 document.querySelector('.page-sections-container') ||
                                 document.querySelector('main') ||
                                 document.body;
            
            console.log('🎯 Initializing section sorting on container:', pageContainer);
            
            if (window.Sortable && pageContainer) {
                window.sectionSortable = Sortable.create(pageContainer, {
                    group: 'sections',
                    animation: 200,
                    ghostClass: 'section-ghost',
                    chosenClass: 'section-chosen',
                    dragClass: 'section-drag',
                    handle: '.section-drag-handle', // Only drag via drag handle
                    filter: '.no-drag', // Exclude certain elements
                    fallbackTolerance: 3,
                    
                    onStart: (evt) => {
                        this.handleSectionDragStart(evt);
                    },
                    
                    onEnd: (evt) => {
                        this.handleSectionDragEnd(evt);
                    },
                    
                    onMove: (evt) => {
                        return this.handleSectionDragMove(evt);
                    }
                });
                
                console.log('✅ Section sorting initialized with SortableJS');
            } else {
                console.warn('⚠️ SortableJS not available or container not found');
            }
        },

        // Handle section drag start
        handleSectionDragStart: function(evt) {
            const section = evt.item;
            const sectionId = section.dataset.previewSection;
            
            console.log('🎯 Section drag started:', sectionId);
            
            // Add visual feedback
            section.classList.add('section-dragging');
            
            // Store original position
            window.originalSectionPosition = evt.oldIndex;
            
            // Notify parent about drag start
            parent.postMessage({
                type: 'section-drag-start',
                data: {
                    sectionId: sectionId,
                    originalPosition: evt.oldIndex
                }
            }, '*');
        },

        // Handle section drag end
        handleSectionDragEnd: function(evt) {
            const section = evt.item;
            const sectionId = section.dataset.previewSection;
            const newPosition = evt.newIndex;
            const oldPosition = window.originalSectionPosition;
            
            console.log('🎯 Section drag ended:', sectionId, 'moved from', oldPosition, 'to', newPosition);
            
            // Remove visual feedback
            section.classList.remove('section-dragging');
            
            if (newPosition !== oldPosition) {
                // Position changed - save to database
                this.saveSectionPosition(sectionId, newPosition, oldPosition);
            }
            
            // Notify parent about drag end
            parent.postMessage({
                type: 'section-drag-end',
                data: {
                    sectionId: sectionId,
                    newPosition: newPosition,
                    oldPosition: oldPosition,
                    positionChanged: newPosition !== oldPosition
                }
            }, '*');
        },

        // Handle section drag move
        handleSectionDragMove: function(evt) {
            // Allow all moves by default
            return true;
        },

        // Save section position to database
        saveSectionPosition: async function(sectionId, newPosition, oldPosition) {
            try {
                console.log(`💾 Saving section position: ${sectionId} from ${oldPosition} to ${newPosition}`);
                
                const response = await fetch('/admin/live-preview/sections/reorder', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        section_id: sectionId,
                        new_position: newPosition,
                        old_position: oldPosition
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    console.log('✅ Section position saved successfully');
                } else {
                    console.error('❌ Failed to save section position:', result.error);
                    // Revert position on failure
                    this.revertSectionPosition(sectionId, oldPosition);
                }
            } catch (error) {
                console.error('❌ Error saving section position:', error);
                this.revertSectionPosition(sectionId, oldPosition);
            }
        },

        // Revert section position on save failure
        revertSectionPosition: function(sectionId, originalPosition) {
            console.log(`🔄 Reverting section position: ${sectionId} to ${originalPosition}`);
            
            if (window.sectionSortable) {
                const section = document.querySelector(`[data-preview-section="${sectionId}"]`);
                if (section) {
                    // Find current position
                    const sections = Array.from(section.parentNode.children);
                    const currentIndex = sections.indexOf(section);
                    
                    // Move back to original position
                    if (currentIndex !== originalPosition) {
                        if (originalPosition < sections.length) {
                            section.parentNode.insertBefore(section, sections[originalPosition]);
                        } else {
                            section.parentNode.appendChild(section);
                        }
                    }
                }
            }
            
            alert('Failed to save section position. The section has been moved back to its original position.');
        },

        // Update toolbar zoom scale to maintain consistent size
        updateToolbarZoomScale: function(section) {
            const toolbar = section.querySelector('.section-toolbar');
            if (toolbar) {
                // Get current iframe zoom level from parent
                const currentZoom = window.parent.livePreview?.getCurrentZoom() || 1;
                const inverseZoom = 1 / currentZoom;
                
                // Apply inverse zoom to maintain consistent toolbar size
                toolbar.style.setProperty('--inverse-zoom', inverseZoom);
                
                console.log(`🔧 Updated toolbar zoom scale: ${inverseZoom} (zoom: ${currentZoom})`);
            }
        },

        // Utility functions for external access
        getSelectedSection: function() {
            const highlighted = document.querySelector('.preview-highlighted[data-preview-section]');
            return highlighted ? {
                sectionId: highlighted.dataset.previewSection,
                sectionName: highlighted.dataset.sectionName
            } : null;
        },

        getAllSections: function() {
            return Array.from(document.querySelectorAll('[data-preview-section]'))
                .map(section => ({
                    sectionId: section.dataset.previewSection,
                    sectionName: section.dataset.sectionName
                }));
        },

        getSectionInfo: function(sectionId) {
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

    // Export to global scope for main coordinator
    window.SectionPreview = SectionPreview;

})();
