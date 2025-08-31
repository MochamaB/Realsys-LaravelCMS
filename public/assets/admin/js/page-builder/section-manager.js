/**
 * Section Manager
 * 
 * Handles all section-related operations including CRUD, positioning,
 * and rendering sections in the GridStack layout.
 */
class SectionManager {
    constructor(api, gridManager, unifiedLoader = null) {
        this.api = api;
        this.gridManager = gridManager;
        this.unifiedLoader = unifiedLoader;
        this.sections = new Map(); // Store sections by ID
        this.sectionElements = new Map(); // Store DOM elements by section ID
        
        console.log('📋 Section Manager initialized');
    }

    /**
     * Load all sections for the current page
     */
    async loadSections() {
        try {
            // Use unified loader if available
            if (this.unifiedLoader) {
                this.unifiedLoader.show('loadSections', 'Loading page sections...', 10);
            }
            
            console.log('🔄 Loading page sections...');
            
            const response = await this.api.getSections();
            
            if (response.success && response.data) {
                // Clear existing sections
                this.sections.clear();
                this.sectionElements.clear();
                
                // Store sections
                response.data.forEach(section => {
                    this.sections.set(section.id, section);
                });
                
                console.log(`✅ Loaded ${response.data.length} sections:`, response.data);
                
                // Hide loader
                if (this.unifiedLoader) {
                    this.unifiedLoader.hide('loadSections');
                }
                
                return response.data;
            } else {
                console.warn('⚠️ No sections found in response');
                if (this.unifiedLoader) {
                    this.unifiedLoader.hide('loadSections');
                }
                return [];
            }
        } catch (error) {
            console.error('❌ Error loading sections:', error);
            if (this.unifiedLoader) {
                this.unifiedLoader.showError('loadSections', 'Failed to load sections');
            }
            throw error;
        }
    }

    /**
     * Create a new section from template
     */
    async createSection(templateKey, options = {}) {
        try {
            console.log('🔨 Creating new section:', { templateKey, options });
            
            const sectionData = {
                template_key: templateKey,
                name: options.name,
                grid_x: options.grid_x || 0,
                grid_y: options.grid_y || 0,
                grid_w: options.grid_w || 12,
                grid_h: options.grid_h || 4,
                ...options
            };

            const response = await this.api.createSection(sectionData);
            
            if (response.success && response.data) {
                const newSection = response.data;
                this.sections.set(newSection.id, newSection);
                
                console.log('✅ Section created:', newSection);
                return newSection;
            } else {
                throw new Error('Failed to create section');
            }
        } catch (error) {
            console.error('❌ Error creating section:', error);
            throw error;
        }
    }

    /**
     * Update section properties
     */
    async updateSection(sectionId, updates) {
        try {
            console.log('📝 Updating section:', { sectionId, updates });
            
            const response = await this.api.updateSection(sectionId, updates);
            
            if (response.success && response.data) {
                const updatedSection = response.data;
                this.sections.set(sectionId, updatedSection);
                
                console.log('✅ Section updated:', updatedSection);
                return updatedSection;
            } else {
                throw new Error('Failed to update section');
            }
        } catch (error) {
            console.error('❌ Error updating section:', error);
            throw error;
        }
    }

    /**
     * Delete a section
     */
    async deleteSection(sectionId) {
        try {
            console.log('🗑️ Deleting section:', sectionId);
            
            const response = await this.api.deleteSection(sectionId);
            
            if (response.success) {
                // Remove from local storage
                this.sections.delete(sectionId);
                
                // Remove DOM element if exists
                const element = this.sectionElements.get(sectionId);
                if (element) {
                    this.gridManager.removeGridItem(element);
                    this.sectionElements.delete(sectionId);
                }
                
                console.log('✅ Section deleted:', sectionId);
                return true;
            } else {
                throw new Error('Failed to delete section');
            }
        } catch (error) {
            console.error('❌ Error deleting section:', error);
            throw error;
        }
    }

    /**
     * Update section position in GridStack
     */
    async updateSectionPosition(sectionId, position) {
        try {
            console.log('📍 Updating section position:', { sectionId, position });
            
            const positionData = {
                grid_x: position.x,
                grid_y: position.y,
                grid_w: position.w,
                grid_h: position.h
            };

            const response = await this.api.updateSectionPosition(sectionId, positionData);
            
            if (response.success) {
                // Update local section data
                const section = this.sections.get(sectionId);
                if (section) {
                    section.grid_x = position.x;
                    section.grid_y = position.y;
                    section.grid_w = position.w;
                    section.grid_h = position.h;
                }
                
                console.log('✅ Section position updated');
                return true;
            } else {
                throw new Error('Failed to update section position');
            }
        } catch (error) {
            console.error('❌ Error updating section position:', error);
            throw error;
        }
    }

    /**
     * Render section as simple HTML (like old implementation)
     */
    renderSection(section) {
        try {
            console.log('🎨 Rendering section:', section);
            
            const container = document.getElementById('gridStackContainer');
            if (!container) {
                throw new Error('GridStack container not found');
            }
            
            const sectionElement = this.createSectionElement(section);
            this.sectionElements.set(section.id, sectionElement);
            
            // Add section with proper layout structure
            this.addSectionToLayout(container, sectionElement, section);
            
            // Attach events
            this.attachSectionEvents(sectionElement, section);
            
            return sectionElement;
        } catch (error) {
            console.error('❌ Error rendering section:', error);
            throw error;
        }
    }

    /**
     * Create section DOM element (matching old implementation)
     */
    createSectionElement(section) {
        const sectionDiv = document.createElement('div');
        sectionDiv.className = 'page-section mb-4';
        sectionDiv.setAttribute('data-section-id', section.id);
        
        const templateSection = section.template_section;
        const sectionName = templateSection?.name || 'Section';
        const sectionType = templateSection?.section_type || 'full-width';
        
        // Use the frontend-like HTML structure
        sectionDiv.innerHTML = this.getSectionHTML(section, sectionType, sectionName);
        
        // Add hover effects to show admin controls
        sectionDiv.addEventListener('mouseenter', () => {
            const adminHeader = sectionDiv.querySelector('.section-admin-header');
            if (adminHeader) {
                adminHeader.style.opacity = '1';
            }
        });
        
        sectionDiv.addEventListener('mouseleave', () => {
            const adminHeader = sectionDiv.querySelector('.section-admin-header');
            if (adminHeader) {
                adminHeader.style.opacity = '0';
            }
        });
        
        return sectionDiv;
    }

    /**
     * Add section to layout container (simplified - no categorization)
     */
    addSectionToLayout(container, sectionElement, section) {
        // Simply append to the main container
        container.appendChild(sectionElement);
        console.log('📍 Added section to layout:', section.id);
    }

    /**
     * Create the overall layout structure (header, main, footer)
     */
    createLayoutStructure(container) {
        console.log('🏗️ Creating layout structure...');
        
        const layoutStructure = document.createElement('div');
        layoutStructure.className = 'layout-structure';
        layoutStructure.style.cssText = `
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: white;
        `;
        
        layoutStructure.innerHTML = `
            <header class="site-header" style="background: #f8f9fa; border-bottom: 1px solid #dee2e6;">
                <div class="header-label text-center py-2 small text-muted">
                    <i class="ri-layout-top-line me-1"></i>Header Sections
                </div>
                <div class="header-sections">
                    <!-- Header sections will be added here -->
                </div>
            </header>
            
            <main class="site-main" style="flex: 1;">
                <div class="page-content">
                    <div class="content-label text-center py-2 small text-muted bg-light border-bottom">
                        <i class="ri-layout-masonry-line me-1"></i>Main Content Sections
                    </div>
                    <div class="content-sections">
                        <!-- Main content sections will be added here -->
                    </div>
                </div>
            </main>
            
            <footer class="site-footer" style="background: #f8f9fa; border-top: 1px solid #dee2e6; margin-top: auto;">
                <div class="footer-sections">
                    <!-- Footer sections will be added here -->
                </div>
                <div class="footer-label text-center py-2 small text-muted">
                    <i class="ri-layout-bottom-line me-1"></i>Footer Sections
                </div>
            </footer>
        `;
        
        // Move any existing content to main area
        const mainContent = layoutStructure.querySelector('.content-sections');
        while (container.firstChild) {
            mainContent.appendChild(container.firstChild);
        }
        
        container.appendChild(layoutStructure);
        
        console.log('✅ Layout structure created');
        return layoutStructure;
    }

    /**
     * Get section HTML for frontend-like appearance
     */
    getSectionHTML(section, sectionType, sectionName) {
        // Create admin header that shows only on hover
        const adminHeader = `
            <div class="section-admin-header" style="
                position: absolute;
                top: -30px;
                left: 0;
                right: 0;
                background: rgba(13, 110, 253, 0.9);
                color: white;
                padding: 4px 12px;
                font-size: 12px;
                border-radius: 4px 4px 0 0;
                opacity: 0;
                transition: opacity 0.2s ease;
                z-index: 5;
            ">
                <div class="d-flex justify-content-between align-items-center">
                    <span>
                        <i class="ri-layout-grid-line me-1"></i>
                        ${sectionName} (${sectionType})
                    </span>
                    <div class="section-admin-controls">
                        <button class="btn btn-xs btn-outline-light edit-section-btn" style="font-size: 10px; padding: 2px 6px;">
                            <i class="ri-edit-line"></i>
                        </button>
                        <button class="btn btn-xs btn-outline-light delete-section-btn ms-1" style="font-size: 10px; padding: 2px 6px;">
                            <i class="ri-delete-line"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;

        // Create section content based on layout type
        let sectionContent = '';
        
        switch (sectionType) {
            case 'full-width':
                sectionContent = `
                    <div class="section-grid" data-section-grid="${section.id}" style="width: 100%;">
                        <!-- Widgets will be loaded here -->
                    </div>
                `;
                break;
                
            case 'multi-column':
                sectionContent = `
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="section-grid" data-section-grid="${section.id}_col1">
                                    <!-- Column 1 widgets -->
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="section-grid" data-section-grid="${section.id}_col2">
                                    <!-- Column 2 widgets -->
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="section-grid" data-section-grid="${section.id}_col3">
                                    <!-- Column 3 widgets -->
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                break;
                
            case 'sidebar-left':
                sectionContent = `
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="section-grid" data-section-grid="${section.id}_sidebar">
                                    <!-- Sidebar widgets -->
                                </div>
                            </div>
                            <div class="col-md-9">
                                <div class="section-grid" data-section-grid="${section.id}_main">
                                    <!-- Main content widgets -->
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                break;
                
            case 'header':
                sectionContent = `
                    <div class="header-content" style="padding: 1rem 0;">
                        <div class="container">
                            <div class="section-grid" data-section-grid="${section.id}">
                                <!-- Header widgets -->
                            </div>
                        </div>
                    </div>
                `;
                break;
                
            case 'footer':
                sectionContent = `
                    <div class="footer-content" style="padding: 2rem 0;">
                        <div class="container">
                            <div class="section-grid" data-section-grid="${section.id}">
                                <!-- Footer widgets -->
                            </div>
                        </div>
                    </div>
                `;
                break;
                
            default:
                sectionContent = `
                    <div class="container">
                        <div class="section-grid" data-section-grid="${section.id}">
                            <!-- Section widgets -->
                        </div>
                    </div>
                `;
        }

        return `
            ${adminHeader}
            <div class="section-content" style="position: relative; padding: 1rem 0;">
                ${sectionContent}
            </div>
        `;
    }

    /**
     * Attach event listeners to section element
     */
    attachSectionEvents(element, section) {
        // Edit section button
        const editBtn = element.querySelector('.edit-section-btn');
        if (editBtn) {
            editBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.handleEditSection(section.id);
            });
        }
        
        // Delete section button
        const deleteBtn = element.querySelector('.delete-section-btn');
        if (deleteBtn) {
            deleteBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.handleDeleteSection(section.id);
            });
        }
    }

    /**
     * Handle edit section action
     */
    handleEditSection(sectionId) {
        console.log('✏️ Edit section requested:', sectionId);
        
        const section = this.sections.get(sectionId);
        if (!section) {
            console.error('❌ Section not found:', sectionId);
            return;
        }
        
        // Open section configuration modal
        this.openSectionConfigModal(section);
    }

    /**
     * Open section configuration modal using existing Blade modal
     */
    async openSectionConfigModal(section) {
        try {
            console.log('🔧 Opening section configuration modal for:', section.id);
            
            // Get the existing Blade modal
            const modal = document.getElementById('sectionConfigModal');
            if (!modal) {
                throw new Error('Section configuration modal not found');
            }
            
            // Store current section reference
            this.currentSection = section;
            
            // Initialize Bootstrap modal
            const bsModal = new bootstrap.Modal(modal, {
                backdrop: 'static',
                keyboard: false
            });
            
            // Load current section configuration into the form
            await this.loadSectionConfigurationIntoForm(section);
            
            // Setup modal event handlers
            this.setupBladeModalEventHandlers(modal, section, bsModal);
            
            // Show modal
            bsModal.show();
            
            console.log('✅ Section configuration modal opened');
            
        } catch (error) {
            console.error('❌ Error opening section configuration modal:', error);
            alert('Failed to open section configuration. Please try again.');
        }
    }

    /**
     * Create section configuration modal HTML
     */
    createSectionConfigModal(section) {
        const modal = document.createElement('div');
        modal.className = 'modal fade section-config-modal';
        modal.id = `sectionConfigModal-${section.id}`;
        modal.tabIndex = -1;
        modal.setAttribute('aria-labelledby', `sectionConfigModalLabel-${section.id}`);
        modal.setAttribute('aria-hidden', 'true');
        
        const sectionName = section.template_section?.name || `Section ${section.id}`;
        
        modal.innerHTML = `
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <!-- Modal Header -->
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="sectionConfigModalLabel-${section.id}">
                            <i class="ri-settings-3-line me-2"></i>
                            Configure Section: ${sectionName}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    
                    <!-- Modal Body with Tabs -->
                    <div class="modal-body p-0">
                        <div class="section-config-container">
                            <!-- Navigation Tabs -->
                            <ul class="nav nav-tabs border-bottom" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#sectionGeneral-${section.id}" type="button" role="tab">
                                        <i class="ri-settings-line me-1"></i>General
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#sectionLayout-${section.id}" type="button" role="tab">
                                        <i class="ri-layout-grid-line me-1"></i>Layout
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#sectionStyling-${section.id}" type="button" role="tab">
                                        <i class="ri-palette-line me-1"></i>Styling
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#sectionAdvanced-${section.id}" type="button" role="tab">
                                        <i class="ri-code-line me-1"></i>Advanced
                                    </button>
                                </li>
                            </ul>
                            
                            <!-- Tab Content -->
                            <div class="tab-content p-4">
                                <!-- General Tab -->
                                <div class="tab-pane fade show active" id="sectionGeneral-${section.id}" role="tabpanel">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="sectionName-${section.id}" class="form-label fw-bold">Section Name</label>
                                            <input type="text" class="form-control" id="sectionName-${section.id}" name="name" 
                                                   placeholder="Enter section name..." value="${sectionName}">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="sectionType-${section.id}" class="form-label fw-bold">Section Type</label>
                                            <select class="form-select" id="sectionType-${section.id}" name="section_type">
                                                <option value="header">Header</option>
                                                <option value="content">Content</option>
                                                <option value="footer">Footer</option>
                                                <option value="sidebar">Sidebar</option>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label for="sectionDescription-${section.id}" class="form-label fw-bold">Description</label>
                                            <textarea class="form-control" id="sectionDescription-${section.id}" name="description" 
                                                      rows="3" placeholder="Enter section description..."></textarea>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Layout Tab -->
                                <div class="tab-pane fade" id="sectionLayout-${section.id}" role="tabpanel">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="columnLayout-${section.id}" class="form-label fw-bold">Column Layout</label>
                                            <select class="form-select" id="columnLayout-${section.id}" name="column_layout">
                                                <option value="full-width">Full Width</option>
                                                <option value="container">Container</option>
                                                <option value="6-6">Two Columns (50-50)</option>
                                                <option value="8-4">Two Columns (66-33)</option>
                                                <option value="4-4-4">Three Columns</option>
                                                <option value="3-3-3-3">Four Columns</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="containerType-${section.id}" class="form-label fw-bold">Container Type</label>
                                            <select class="form-select" id="containerType-${section.id}" name="container_type">
                                                <option value="container">Container</option>
                                                <option value="container-fluid">Container Fluid</option>
                                                <option value="none">No Container</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="paddingTop-${section.id}" class="form-label fw-bold">Padding Top</label>
                                            <select class="form-select" id="paddingTop-${section.id}" name="padding_top">
                                                <option value="0">None</option>
                                                <option value="1">Small</option>
                                                <option value="3">Medium</option>
                                                <option value="5">Large</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="paddingBottom-${section.id}" class="form-label fw-bold">Padding Bottom</label>
                                            <select class="form-select" id="paddingBottom-${section.id}" name="padding_bottom">
                                                <option value="0">None</option>
                                                <option value="1">Small</option>
                                                <option value="3">Medium</option>
                                                <option value="5">Large</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="marginBottom-${section.id}" class="form-label fw-bold">Margin Bottom</label>
                                            <select class="form-select" id="marginBottom-${section.id}" name="margin_bottom">
                                                <option value="0">None</option>
                                                <option value="2">Small</option>
                                                <option value="4">Medium</option>
                                                <option value="5">Large</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Styling Tab -->
                                <div class="tab-pane fade" id="sectionStyling-${section.id}" role="tabpanel">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="backgroundType-${section.id}" class="form-label fw-bold">Background Type</label>
                                            <select class="form-select" id="backgroundType-${section.id}" name="background_type">
                                                <option value="none">None</option>
                                                <option value="color">Solid Color</option>
                                                <option value="gradient">Gradient</option>
                                                <option value="image">Background Image</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="backgroundColor-${section.id}" class="form-label fw-bold">Background Color</label>
                                            <input type="color" class="form-control form-control-color" id="backgroundColor-${section.id}" name="background_color" value="#ffffff">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="textColor-${section.id}" class="form-label fw-bold">Text Color</label>
                                            <input type="color" class="form-control form-control-color" id="textColor-${section.id}" name="text_color" value="#000000">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="borderStyle-${section.id}" class="form-label fw-bold">Border Style</label>
                                            <select class="form-select" id="borderStyle-${section.id}" name="border_style">
                                                <option value="none">None</option>
                                                <option value="solid">Solid</option>
                                                <option value="dashed">Dashed</option>
                                                <option value="dotted">Dotted</option>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label for="customCSS-${section.id}" class="form-label fw-bold">Custom CSS Classes</label>
                                            <input type="text" class="form-control" id="customCSS-${section.id}" name="custom_css_classes" 
                                                   placeholder="Enter custom CSS classes...">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Advanced Tab -->
                                <div class="tab-pane fade" id="sectionAdvanced-${section.id}" role="tabpanel">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label for="sectionId-${section.id}" class="form-label fw-bold">Section ID</label>
                                            <input type="text" class="form-control" id="sectionId-${section.id}" name="section_id" 
                                                   placeholder="unique-section-id">
                                        </div>
                                        <div class="col-12">
                                            <label for="visibility-${section.id}" class="form-label fw-bold">Visibility Settings</label>
                                            <div class="form-check-group">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="visibleDesktop-${section.id}" name="visible_desktop" checked>
                                                    <label class="form-check-label" for="visibleDesktop-${section.id}">
                                                        <i class="ri-computer-line me-1"></i>Desktop
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="visibleTablet-${section.id}" name="visible_tablet" checked>
                                                    <label class="form-check-label" for="visibleTablet-${section.id}">
                                                        <i class="ri-tablet-line me-1"></i>Tablet
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="visibleMobile-${section.id}" name="visible_mobile" checked>
                                                    <label class="form-check-label" for="visibleMobile-${section.id}">
                                                        <i class="ri-smartphone-line me-1"></i>Mobile
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <label for="customAttributes-${section.id}" class="form-label fw-bold">Custom HTML Attributes</label>
                                            <textarea class="form-control font-monospace" id="customAttributes-${section.id}" name="custom_attributes" 
                                                      rows="3" placeholder='data-aos="fade-up" data-delay="100"'></textarea>
                                            <div class="form-text">Enter HTML attributes in key="value" format, one per line</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Modal Footer -->
                    <div class="modal-footer bg-light">
                        <div class="d-flex justify-content-between w-100">
                            <div>
                                <button type="button" class="btn btn-outline-danger" id="deleteSectionBtn-${section.id}">
                                    <i class="ri-delete-line me-1"></i>Delete Section
                                </button>
                            </div>
                            <div>
                                <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">
                                    <i class="ri-close-line me-1"></i>Cancel
                                </button>
                                <button type="button" class="btn btn-primary" id="saveSectionConfigBtn-${section.id}">
                                    <i class="ri-save-line me-1"></i>Save Changes
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        return modal;
    }

    /**
     * Load section configuration into the Blade modal form
     */
    async loadSectionConfigurationIntoForm(section) {
        try {
            console.log('📄 Loading section configuration into form:', section.id);
            
            // Get current configuration from section or API
            let config = section.config || {};
            
            // If no config exists locally, try to fetch from API
            if (!section.config || Object.keys(section.config).length === 0) {
                try {
                    const response = await this.api.getSectionConfiguration(section.id);
                    if (response.success && response.data) {
                        config = response.data;
                        // Update section with fetched config
                        section.config = config;
                    }
                } catch (error) {
                    console.warn('⚠️ Could not fetch section configuration, using defaults');
                }
            }
            
            // Populate the Blade modal form fields
            this.populateBladeModalForm(section, config);
            
            console.log('✅ Section configuration loaded into form');
            
        } catch (error) {
            console.error('❌ Error loading section configuration:', error);
        }
    }

    /**
     * Populate the Blade modal form with section data
     */
    populateBladeModalForm(section, config) {
        console.log('🔄 Populating Blade modal form with:', { section, config });
        
        // Update modal title
        const modalTitle = document.getElementById('sectionConfigModalLabel');
        if (modalTitle) {
            const sectionName = section.template_section?.name || `Section ${section.id}`;
            modalTitle.innerHTML = `<i class="ri-settings-3-line me-2"></i>Section Configuration: ${sectionName}`;
        }
        
        // Configuration Tab Fields
        // Hidden section ID
        const sectionIdInput = document.getElementById('sectionId');
        if (sectionIdInput) sectionIdInput.value = section.id;
        
        // Section Name
        const sectionNameInput = document.getElementById('sectionName');
        if (sectionNameInput) sectionNameInput.value = config.name || section.template_section?.name || `Section ${section.id}`;
        
        // Position
        const positionInput = document.getElementById('sectionPosition');
        if (positionInput) positionInput.value = config.position || section.position || 0;
        
        // Allows Widgets
        const allowsWidgetsInput = document.getElementById('allowsWidgets');
        if (allowsWidgetsInput) allowsWidgetsInput.checked = config.allows_widgets !== false && section.allows_widgets !== false;
        
        // Widget Types (handle JSON data)
        this.populateWidgetTypes(config.widget_types || section.widget_types);
        
        // Styling & Grid Tab Fields
        // Grid Settings
        const gridXInput = document.getElementById('gridX');
        if (gridXInput) gridXInput.value = config.grid_x || section.grid_x || 0;
        
        const gridYInput = document.getElementById('gridY');
        if (gridYInput) gridYInput.value = config.grid_y || section.grid_y || 0;
        
        const gridWInput = document.getElementById('gridW');
        if (gridWInput) gridWInput.value = config.grid_w || section.grid_w || 12;
        
        const gridHInput = document.getElementById('gridH');
        if (gridHInput) gridHInput.value = config.grid_h || section.grid_h || 4;
        
        // Column overrides
        const columnSpanInput = document.getElementById('columnSpanOverride');
        if (columnSpanInput) columnSpanInput.value = config.column_span_override || '';
        
        const columnOffsetInput = document.getElementById('columnOffsetOverride');
        if (columnOffsetInput) columnOffsetInput.value = config.column_offset_override || '';
        
        // Lock position
        const lockedPositionInput = document.getElementById('lockedPosition');
        if (lockedPositionInput) lockedPositionInput.checked = config.locked_position || section.locked_position || false;
        
        // Styling Settings
        const cssClassesInput = document.getElementById('cssClasses');
        if (cssClassesInput) cssClassesInput.value = config.css_classes || section.css_classes || '';
        
        const backgroundColorInput = document.getElementById('backgroundColor');
        const backgroundColorTextInput = document.getElementById('backgroundColorText');
        const bgColor = config.background_color || section.background_color || '#ffffff';
        if (backgroundColorInput) backgroundColorInput.value = bgColor;
        if (backgroundColorTextInput) backgroundColorTextInput.value = bgColor;
        
        // Separated Padding inputs
        const paddingTopInput = document.getElementById('paddingTop');
        const paddingBottomInput = document.getElementById('paddingBottom');
        const paddingLeftInput = document.getElementById('paddingLeft');
        const paddingRightInput = document.getElementById('paddingRight');
        
        if (paddingTopInput) paddingTopInput.value = config.padding_top || section.padding_top || 0;
        if (paddingBottomInput) paddingBottomInput.value = config.padding_bottom || section.padding_bottom || 0;
        if (paddingLeftInput) paddingLeftInput.value = config.padding_left || section.padding_left || 0;
        if (paddingRightInput) paddingRightInput.value = config.padding_right || section.padding_right || 0;
        
        // Separated Margin inputs
        const marginTopInput = document.getElementById('marginTop');
        const marginBottomInput = document.getElementById('marginBottom');
        const marginLeftInput = document.getElementById('marginLeft');
        const marginRightInput = document.getElementById('marginRight');
        
        if (marginTopInput) marginTopInput.value = config.margin_top || section.margin_top || 0;
        if (marginBottomInput) marginBottomInput.value = config.margin_bottom || section.margin_bottom || 0;
        if (marginLeftInput) marginLeftInput.value = config.margin_left || section.margin_left || 0;
        if (marginRightInput) marginRightInput.value = config.margin_right || section.margin_right || 0;
        
        const resizeHandlesInput = document.getElementById('resizeHandles');
        if (resizeHandlesInput) resizeHandlesInput.value = config.resize_handles || section.resize_handles || 'all';
        
        console.log('✅ Blade modal form populated');
    }

    /**
     * Populate widget types checkboxes
     */
    populateWidgetTypes(widgetTypes) {
        const container = document.getElementById('widgetTypesContainer');
        if (!container) return;
        
        // Show loading initially
        container.innerHTML = '<div class="text-muted text-center py-2"><i class="ri-loader-4-line spin"></i> Loading widget types...</div>';
        
        // Get available widget types (this would typically come from an API)
        // For now, use common widget types
        const availableWidgetTypes = [
            { key: 'text', name: 'Text Widget', description: 'Rich text content' },
            { key: 'image', name: 'Image Widget', description: 'Image display' },
            { key: 'gallery', name: 'Gallery Widget', description: 'Image gallery' },
            { key: 'video', name: 'Video Widget', description: 'Video embed' },
            { key: 'button', name: 'Button Widget', description: 'Call-to-action button' },
            { key: 'form', name: 'Form Widget', description: 'Contact forms' },
            { key: 'map', name: 'Map Widget', description: 'Google Maps' },
            { key: 'social', name: 'Social Widget', description: 'Social media links' }
        ];
        
        // Parse current widget types
        let selectedTypes = [];
        if (typeof widgetTypes === 'string') {
            try {
                selectedTypes = JSON.parse(widgetTypes);
            } catch (e) {
                selectedTypes = widgetTypes.split(',').map(t => t.trim());
            }
        } else if (Array.isArray(widgetTypes)) {
            selectedTypes = widgetTypes;
        }
        
        // Create checkboxes
        let html = '';
        availableWidgetTypes.forEach(type => {
            const isChecked = selectedTypes.length === 0 || selectedTypes.includes(type.key);
            html += `
                <div class="form-check">
                    <input class="form-check-input widget-type-checkbox" type="checkbox" 
                           id="widgetType_${type.key}" value="${type.key}" ${isChecked ? 'checked' : ''}>
                    <label class="form-check-label" for="widgetType_${type.key}">
                        <strong>${type.name}</strong>
                        <div class="form-text">${type.description}</div>
                    </label>
                </div>
            `;
        });
        
        container.innerHTML = html;
        
        // Update hidden field when checkboxes change
        this.updateWidgetTypesJson();
        
        // Add event listeners
        container.querySelectorAll('.widget-type-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', () => this.updateWidgetTypesJson());
        });
    }

    /**
     * Update the hidden widget types JSON field
     */
    updateWidgetTypesJson() {
        const checkboxes = document.querySelectorAll('.widget-type-checkbox:checked');
        const selectedTypes = Array.from(checkboxes).map(cb => cb.value);
        
        const hiddenField = document.getElementById('widgetTypesJson');
        if (hiddenField) {
            hiddenField.value = JSON.stringify(selectedTypes);
        }
    }

    /**
     * Populate section configuration form with current values
     */
    populateSectionConfigForm(modal, section, config) {
        const sectionId = section.id;
        
        // General tab
        const nameInput = modal.querySelector(`#sectionName-${sectionId}`);
        if (nameInput) nameInput.value = config.name || section.template_section?.name || '';
        
        const typeSelect = modal.querySelector(`#sectionType-${sectionId}`);
        if (typeSelect) typeSelect.value = config.section_type || section.template_section?.section_type || 'content';
        
        const descInput = modal.querySelector(`#sectionDescription-${sectionId}`);
        if (descInput) descInput.value = config.description || '';
        
        // Layout tab
        const layoutSelect = modal.querySelector(`#columnLayout-${sectionId}`);
        if (layoutSelect) layoutSelect.value = config.column_layout || 'full-width';
        
        const containerSelect = modal.querySelector(`#containerType-${sectionId}`);
        if (containerSelect) containerSelect.value = config.container_type || 'container';
        
        const paddingTopSelect = modal.querySelector(`#paddingTop-${sectionId}`);
        if (paddingTopSelect) paddingTopSelect.value = config.padding_top || '3';
        
        const paddingBottomSelect = modal.querySelector(`#paddingBottom-${sectionId}`);
        if (paddingBottomSelect) paddingBottomSelect.value = config.padding_bottom || '3';
        
        const marginBottomSelect = modal.querySelector(`#marginBottom-${sectionId}`);
        if (marginBottomSelect) marginBottomSelect.value = config.margin_bottom || '4';
        
        // Styling tab
        const bgTypeSelect = modal.querySelector(`#backgroundType-${sectionId}`);
        if (bgTypeSelect) bgTypeSelect.value = config.background_type || 'none';
        
        const bgColorInput = modal.querySelector(`#backgroundColor-${sectionId}`);
        if (bgColorInput) bgColorInput.value = config.background_color || '#ffffff';
        
        const textColorInput = modal.querySelector(`#textColor-${sectionId}`);
        if (textColorInput) textColorInput.value = config.text_color || '#000000';
        
        const borderSelect = modal.querySelector(`#borderStyle-${sectionId}`);
        if (borderSelect) borderSelect.value = config.border_style || 'none';
        
        const customCSSInput = modal.querySelector(`#customCSS-${sectionId}`);
        if (customCSSInput) customCSSInput.value = config.custom_css_classes || '';
        
        // Advanced tab
        const sectionIdInput = modal.querySelector(`#sectionId-${sectionId}`);
        if (sectionIdInput) sectionIdInput.value = config.section_html_id || '';
        
        const visibleDesktop = modal.querySelector(`#visibleDesktop-${sectionId}`);
        if (visibleDesktop) visibleDesktop.checked = config.visible_desktop !== false;
        
        const visibleTablet = modal.querySelector(`#visibleTablet-${sectionId}`);
        if (visibleTablet) visibleTablet.checked = config.visible_tablet !== false;
        
        const visibleMobile = modal.querySelector(`#visibleMobile-${sectionId}`);
        if (visibleMobile) visibleMobile.checked = config.visible_mobile !== false;
        
        const customAttrsInput = modal.querySelector(`#customAttributes-${sectionId}`);
        if (customAttrsInput) customAttrsInput.value = config.custom_attributes || '';
    }

    /**
     * Setup event handlers for the Blade modal
     */
    setupBladeModalEventHandlers(modal, section, bsModal) {
        // Remove any existing event listeners to prevent duplicates
        this.removeBladeModalEventHandlers();
        
        // Save button handler
        const saveBtn = document.getElementById('saveSectionBtn');
        if (saveBtn) {
            this.saveBtnHandler = async () => {
                await this.saveSectionConfigurationFromForm(section, bsModal);
            };
            saveBtn.addEventListener('click', this.saveBtnHandler);
        }
        
        // Delete button handler
        const deleteBtn = document.getElementById('deleteSectionBtn');
        if (deleteBtn) {
            this.deleteBtnHandler = async () => {
                await this.confirmDeleteSectionFromForm(section, bsModal);
            };
            deleteBtn.addEventListener('click', this.deleteBtnHandler);
        }
        
        // Background color sync handler
        const backgroundColorInput = document.getElementById('backgroundColor');
        const backgroundColorTextInput = document.getElementById('backgroundColorText');
        
        if (backgroundColorInput && backgroundColorTextInput) {
            this.bgColorHandler = (e) => {
                backgroundColorTextInput.value = e.target.value;
            };
            this.bgColorTextHandler = (e) => {
                if (/^#[0-9A-F]{6}$/i.test(e.target.value)) {
                    backgroundColorInput.value = e.target.value;
                }
            };
            
            backgroundColorInput.addEventListener('input', this.bgColorHandler);
            backgroundColorTextInput.addEventListener('input', this.bgColorTextHandler);
        }
        
        // Modal cleanup on hide
        this.modalHideHandler = () => {
            this.removeBladeModalEventHandlers();
            this.currentSection = null;
        };
        modal.addEventListener('hidden.bs.modal', this.modalHideHandler);
    }

    /**
     * Remove event handlers to prevent memory leaks
     */
    removeBladeModalEventHandlers() {
        const saveBtn = document.getElementById('saveSectionBtn');
        const deleteBtn = document.getElementById('deleteSectionBtn');
        const backgroundColorInput = document.getElementById('backgroundColor');
        const backgroundColorTextInput = document.getElementById('backgroundColorText');
        const modal = document.getElementById('sectionConfigModal');
        
        if (saveBtn && this.saveBtnHandler) {
            saveBtn.removeEventListener('click', this.saveBtnHandler);
        }
        if (deleteBtn && this.deleteBtnHandler) {
            deleteBtn.removeEventListener('click', this.deleteBtnHandler);
        }
        if (backgroundColorInput && this.bgColorHandler) {
            backgroundColorInput.removeEventListener('input', this.bgColorHandler);
        }
        if (backgroundColorTextInput && this.bgColorTextHandler) {
            backgroundColorTextInput.removeEventListener('input', this.bgColorTextHandler);
        }
        if (modal && this.modalHideHandler) {
            modal.removeEventListener('hidden.bs.modal', this.modalHideHandler);
        }
    }

    /**
     * Save section configuration from Blade modal form
     */
    async saveSectionConfigurationFromForm(section, bsModal) {
        try {
            console.log('💾 Saving section configuration from form:', section.id);
            
            // Show loading state
            const saveBtn = document.getElementById('saveSectionBtn');
            const saveSpinner = document.getElementById('saveSpinner');
            const originalText = saveBtn.innerHTML;
            
            // Collect form data from the modal form
            const configData = {
                position: parseInt(document.getElementById('sectionPosition')?.value) || 0,
                background_color: document.getElementById('backgroundColor')?.value || '',
                css_classes: document.getElementById('cssClasses')?.value || '',
                // Collect individual padding values and convert to JSON string
                padding: JSON.stringify({
                    top: parseInt(document.getElementById('paddingTop')?.value) || 0,
                    bottom: parseInt(document.getElementById('paddingBottom')?.value) || 0,
                    left: parseInt(document.getElementById('paddingLeft')?.value) || 0,
                    right: parseInt(document.getElementById('paddingRight')?.value) || 0
                }),
                // Collect individual margin values and convert to JSON string  
                margin: JSON.stringify({
                    top: parseInt(document.getElementById('marginTop')?.value) || 0,
                    bottom: parseInt(document.getElementById('marginBottom')?.value) || 0,
                    left: parseInt(document.getElementById('marginLeft')?.value) || 0,
                    right: parseInt(document.getElementById('marginRight')?.value) || 0
                }),
                column_span_override: document.getElementById('columnSpanOverride')?.value || null,
                column_offset_override: document.getElementById('columnOffsetOverride')?.value || null,
                // Grid settings
                grid_x: parseInt(document.getElementById('gridX')?.value) || 0,
                grid_y: parseInt(document.getElementById('gridY')?.value) || 0,
                grid_w: parseInt(document.getElementById('gridW')?.value) || 12,
                grid_h: parseInt(document.getElementById('gridH')?.value) || 4,
                locked_position: document.getElementById('lockedPosition')?.checked || false,
                allows_widgets: document.getElementById('allowsWidgets')?.checked || true
            };
            
            const response = await this.api.updateSection(section.id, configData);
            
            if (response.success) {
                // Update local section data
                section.config = { ...section.config, ...configData };
                
                // Update progress
                if (this.unifiedLoader) {
                    this.unifiedLoader.setProgress(60);
                    this.unifiedLoader.updateMessage('Refreshing section display...');
                }
                
                // Refresh section rendering
                await this.refreshSectionDisplay(section);
                
                // Update progress
                if (this.unifiedLoader) {
                    this.unifiedLoader.setProgress(80);
                    this.unifiedLoader.updateMessage('Reloading preview...');
                }
                
                // Reload the preview to show changes
                await this.reloadPreview();
                
                // Show success message
                console.log('✅ Section configuration updated successfully');
                
                // Complete and hide loader
                if (this.unifiedLoader) {
                    this.unifiedLoader.setProgress(100);
                    this.unifiedLoader.hide('saveSection');
                }
                
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('sectionConfigModal'));
                if (modal) {
                    modal.hide();
                }
                
                console.log(' Section configuration saved successfully');
                
            } else {
                throw new Error(response.message || 'Failed to save section configuration');
            }
            
        } catch (error) {
            console.error(' Error saving section configuration:', error);
            if (this.unifiedLoader) {
                this.unifiedLoader.showError('saveSection', 'Failed to save configuration');
            }
            this.showBladeModalErrorMessage('Failed to save configuration. Please try again.');
        } finally {
            // Restore button state
            const saveBtn = document.getElementById('saveSectionBtn');
            const saveSpinner = document.getElementById('saveSpinner');
            
            if (saveSpinner) saveSpinner.classList.add('d-none');
            if (saveBtn) {
                saveBtn.disabled = false;
            }
        }
    }

    /**
     * Collect form data from Blade modal
     */
    collectBladeModalFormData() {
        const data = {};
        
        // Configuration tab
        const sectionNameInput = document.getElementById('sectionName');
        if (sectionNameInput) data.name = sectionNameInput.value.trim();
        
        const positionInput = document.getElementById('sectionPosition');
        if (positionInput) data.position = parseInt(positionInput.value) || 0;
        
        const allowsWidgetsInput = document.getElementById('allowsWidgets');
        if (allowsWidgetsInput) data.allows_widgets = allowsWidgetsInput.checked;
        
        const widgetTypesJson = document.getElementById('widgetTypesJson');
        if (widgetTypesJson && widgetTypesJson.value) {
            try {
                data.widget_types = JSON.parse(widgetTypesJson.value);
            } catch (e) {
                data.widget_types = [];
            }
        }
        
        // Styling & Grid tab
        const gridXInput = document.getElementById('gridX');
        if (gridXInput) data.grid_x = parseInt(gridXInput.value) || 0;
        
        const gridYInput = document.getElementById('gridY');
        if (gridYInput) data.grid_y = parseInt(gridYInput.value) || 0;
        
        const gridWInput = document.getElementById('gridW');
        if (gridWInput) data.grid_w = parseInt(gridWInput.value) || 12;
        
        const gridHInput = document.getElementById('gridH');
        if (gridHInput) data.grid_h = parseInt(gridHInput.value) || 4;
        
        const columnSpanInput = document.getElementById('columnSpanOverride');
        if (columnSpanInput) data.column_span_override = columnSpanInput.value.trim();
        
        const columnOffsetInput = document.getElementById('columnOffsetOverride');
        if (columnOffsetInput) data.column_offset_override = columnOffsetInput.value.trim();
        
        const lockedPositionInput = document.getElementById('lockedPosition');
        if (lockedPositionInput) data.locked_position = lockedPositionInput.checked;
        
        const cssClassesInput = document.getElementById('cssClasses');
        if (cssClassesInput) data.css_classes = cssClassesInput.value.trim();
        
        const backgroundColorInput = document.getElementById('backgroundColor');
        if (backgroundColorInput) data.background_color = backgroundColorInput.value;
        
        // Collect separated padding values
        const paddingTopInput = document.getElementById('paddingTop');
        const paddingBottomInput = document.getElementById('paddingBottom');
        const paddingLeftInput = document.getElementById('paddingLeft');
        const paddingRightInput = document.getElementById('paddingRight');
        
        if (paddingTopInput) data.padding_top = parseInt(paddingTopInput.value) || 0;
        if (paddingBottomInput) data.padding_bottom = parseInt(paddingBottomInput.value) || 0;
        if (paddingLeftInput) data.padding_left = parseInt(paddingLeftInput.value) || 0;
        if (paddingRightInput) data.padding_right = parseInt(paddingRightInput.value) || 0;
        
        // Collect separated margin values
        const marginTopInput = document.getElementById('marginTop');
        const marginBottomInput = document.getElementById('marginBottom');
        const marginLeftInput = document.getElementById('marginLeft');
        const marginRightInput = document.getElementById('marginRight');
        
        if (marginTopInput) data.margin_top = parseInt(marginTopInput.value) || 0;
        if (marginBottomInput) data.margin_bottom = parseInt(marginBottomInput.value) || 0;
        if (marginLeftInput) data.margin_left = parseInt(marginLeftInput.value) || 0;
        if (marginRightInput) data.margin_right = parseInt(marginRightInput.value) || 0;
        
        const resizeHandlesInput = document.getElementById('resizeHandles');
        if (resizeHandlesInput) data.resize_handles = resizeHandlesInput.value;
        
        console.log('📋 Collected form data:', data);
        return data;
    }

    /**
     * Confirm delete section from Blade modal
     */
    async confirmDeleteSectionFromForm(section, bsModal) {
        if (confirm(`Are you sure you want to delete this section? This action cannot be undone.`)) {
            try {
                await this.deleteSection(section.id);
                bsModal.hide();
                
                // Emit event for page builder to refresh
                document.dispatchEvent(new CustomEvent('pagebuilder:section-deleted', {
                    detail: { sectionId: section.id }
                }));
                
                console.log('✅ Section deleted successfully');
            } catch (error) {
                console.error('❌ Error deleting section:', error);
                this.showBladeModalErrorMessage('Failed to delete section. Please try again.');
            }
        }
    }

    /**
     * Show success message in Blade modal
     */
    showBladeModalSuccessMessage(message) {
        const alertDiv = document.getElementById('sectionConfigAlert');
        const alertMessage = document.getElementById('alertMessage');
        
        if (alertDiv && alertMessage) {
            alertDiv.className = 'alert alert-success alert-dismissible fade show';
            alertMessage.innerHTML = `<i class="ri-check-line me-2"></i>${message}`;
            alertDiv.classList.remove('d-none');
            
            // Auto-hide after 3 seconds
            setTimeout(() => {
                alertDiv.classList.add('d-none');
            }, 3000);
        }
    }

    /**
     * Show error message in Blade modal
     */
    showBladeModalErrorMessage(message) {
        const alertDiv = document.getElementById('sectionConfigAlert');
        const alertMessage = document.getElementById('alertMessage');
        
        if (alertDiv && alertMessage) {
            alertDiv.className = 'alert alert-danger alert-dismissible fade show';
            alertMessage.innerHTML = `<i class="ri-error-warning-line me-2"></i>${message}`;
            alertDiv.classList.remove('d-none');
        }
    }

    /**
     * Collect section configuration data from modal form
     */
    collectSectionConfigData(modal, sectionId) {
        const data = {};
        
        // General tab
        const nameInput = modal.querySelector(`#sectionName-${sectionId}`);
        if (nameInput) data.name = nameInput.value.trim();
        
        const typeSelect = modal.querySelector(`#sectionType-${sectionId}`);
        if (typeSelect) data.section_type = typeSelect.value;
        
        const descInput = modal.querySelector(`#sectionDescription-${sectionId}`);
        if (descInput) data.description = descInput.value.trim();
        
        // Layout tab
        const layoutSelect = modal.querySelector(`#columnLayout-${sectionId}`);
        if (layoutSelect) data.column_layout = layoutSelect.value;
        
        const containerSelect = modal.querySelector(`#containerType-${sectionId}`);
        if (containerSelect) data.container_type = containerSelect.value;
        
        const paddingTopSelect = modal.querySelector(`#paddingTop-${sectionId}`);
        if (paddingTopSelect) data.padding_top = paddingTopSelect.value;
        
        const paddingBottomSelect = modal.querySelector(`#paddingBottom-${sectionId}`);
        if (paddingBottomSelect) data.padding_bottom = paddingBottomSelect.value;
        
        const marginBottomSelect = modal.querySelector(`#marginBottom-${sectionId}`);
        if (marginBottomSelect) data.margin_bottom = marginBottomSelect.value;
        
        // Styling tab
        const bgTypeSelect = modal.querySelector(`#backgroundType-${sectionId}`);
        if (bgTypeSelect) data.background_type = bgTypeSelect.value;
        
        const bgColorInput = modal.querySelector(`#backgroundColor-${sectionId}`);
        if (bgColorInput) data.background_color = bgColorInput.value;
        
        const textColorInput = modal.querySelector(`#textColor-${sectionId}`);
        if (textColorInput) data.text_color = textColorInput.value;
        
        const borderSelect = modal.querySelector(`#borderStyle-${sectionId}`);
        if (borderSelect) data.border_style = borderSelect.value;
        
        const customCSSInput = modal.querySelector(`#customCSS-${sectionId}`);
        if (customCSSInput) data.custom_css_classes = customCSSInput.value.trim();
        
        // Advanced tab
        const sectionIdInput = modal.querySelector(`#sectionId-${sectionId}`);
        if (sectionIdInput) data.section_html_id = sectionIdInput.value.trim();
        
        const visibleDesktop = modal.querySelector(`#visibleDesktop-${sectionId}`);
        if (visibleDesktop) data.visible_desktop = visibleDesktop.checked;
        
        const visibleTablet = modal.querySelector(`#visibleTablet-${sectionId}`);
        if (visibleTablet) data.visible_tablet = visibleTablet.checked;
        
        const visibleMobile = modal.querySelector(`#visibleMobile-${sectionId}`);
        if (visibleMobile) data.visible_mobile = visibleMobile.checked;
        
        const customAttrsInput = modal.querySelector(`#customAttributes-${sectionId}`);
        if (customAttrsInput) data.custom_attributes = customAttrsInput.value.trim();
        
        return data;
    }

    /**
     * Refresh section display after configuration changes
     */
    async refreshSectionDisplay(section) {
        try {
            console.log('🔄 Refreshing section display:', section.id);
            
            const sectionElement = this.sectionElements.get(section.id);
            if (sectionElement) {
                // Update section name in admin header
                const adminHeader = sectionElement.querySelector('.section-admin-header span');
                if (adminHeader && section.config?.name) {
                    adminHeader.innerHTML = `
                        <i class="ri-layout-grid-line me-1"></i>
                        ${section.config.name} (${section.config.section_type || 'content'})
                    `;
                }
                
                // Apply styling changes if any
                if (section.config) {
                    this.applySectionStyling(sectionElement, section.config);
                }
            }
            
            // Emit event for other components to handle
            document.dispatchEvent(new CustomEvent('pagebuilder:section-updated', {
                detail: { sectionId: section.id, section: section }
            }));
            
            console.log('✅ Section display refreshed');
            
        } catch (error) {
            console.error('❌ Error refreshing section display:', error);
        }
    }

    /**
     * Apply section styling configuration to element
     */
    applySectionStyling(sectionElement, config) {
        const sectionContent = sectionElement.querySelector('.section-content');
        if (!sectionContent) return;
        
        // Apply background styling
        if (config.background_type === 'color' && config.background_color) {
            sectionContent.style.backgroundColor = config.background_color;
        } else if (config.background_type === 'none') {
            sectionContent.style.backgroundColor = '';
        }
        
        // Apply text color
        if (config.text_color && config.text_color !== '#000000') {
            sectionContent.style.color = config.text_color;
        } else {
            sectionContent.style.color = '';
        }
        
        // Apply padding
        const paddingTop = config.padding_top ? `${config.padding_top}rem` : '';
        const paddingBottom = config.padding_bottom ? `${config.padding_bottom}rem` : '';
        if (paddingTop || paddingBottom) {
            sectionContent.style.paddingTop = paddingTop;
            sectionContent.style.paddingBottom = paddingBottom;
        }
        
        // Apply margin bottom
        if (config.margin_bottom && config.margin_bottom !== '4') {
            sectionElement.style.marginBottom = `${config.margin_bottom}rem`;
        }
        
        // Apply custom CSS classes
        if (config.custom_css_classes) {
            const classes = config.custom_css_classes.split(' ').filter(cls => cls.trim());
            sectionElement.classList.add(...classes);
        }
    }

    /**
     * Handle background type change in modal
     */
    handleBackgroundTypeChange(modal, sectionId, backgroundType) {
        const bgColorContainer = modal.querySelector(`#backgroundColor-${sectionId}`).closest('.col-md-6');
        
        if (backgroundType === 'none') {
            bgColorContainer.style.display = 'none';
        } else {
            bgColorContainer.style.display = 'block';
        }
    }

    /**
     * Show success message in modal
     */
    showConfigSuccessMessage(modal, message) {
        const existingAlert = modal.querySelector('.config-alert');
        if (existingAlert) {
            existingAlert.remove();
        }
        
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-success config-alert';
        alertDiv.innerHTML = `
            <i class="ri-check-line me-1"></i>
            ${message}
        `;
        
        const modalBody = modal.querySelector('.modal-body');
        modalBody.insertBefore(alertDiv, modalBody.firstChild);
        
        // Auto-remove alert after 3 seconds
        setTimeout(() => {
            alertDiv.remove();
        }, 3000);
    }

    /**
     * Show error message in modal
     */
    showConfigErrorMessage(modal, message) {
        const existingAlert = modal.querySelector('.config-alert');
        if (existingAlert) {
            existingAlert.remove();
        }
        
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-danger config-alert';
        alertDiv.innerHTML = `
            <i class="ri-error-warning-line me-1"></i>
            ${message}
        `;
        
        const modalBody = modal.querySelector('.modal-body');
        modalBody.insertBefore(alertDiv, modalBody.firstChild);
    }

    /**
     * Confirm section deletion
     */
    async confirmDeleteSection(section, bsModal) {
        const sectionName = section.template_section?.name || `Section ${section.id}`;
        const confirmed = confirm(`Are you sure you want to delete "${sectionName}"?\n\nThis action cannot be undone and will also delete all widgets in this section.`);
        
        if (confirmed) {
            try {
                await this.deleteSection(section.id);
                bsModal.hide();
                
                // Show success notification
                this.showGlobalNotification('Section deleted successfully!', 'success');
                
            } catch (error) {
                console.error('❌ Error deleting section:', error);
                alert('Failed to delete section. Please try again.');
            }
        }
    }

    /**
     * Show global notification (using toasts if available)
     */
    showGlobalNotification(message, type = 'info') {
        // Use Bootstrap toast if available, otherwise alert
        if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
            const toastHtml = `
                <div class="toast align-items-center text-bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            ${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            `;
            
            // Find or create toast container
            let toastContainer = document.querySelector('.toast-container');
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
                toastContainer.style.zIndex = '9999';
                document.body.appendChild(toastContainer);
            }
            
            const toastElement = document.createElement('div');
            toastElement.innerHTML = toastHtml;
            const toast = toastElement.firstElementChild;
            
            toastContainer.appendChild(toast);
            const bsToast = new bootstrap.Toast(toast);
            bsToast.show();
            
            // Auto-remove after it's hidden
            toast.addEventListener('hidden.bs.toast', () => {
                toast.remove();
            });
        } else {
            // Fallback to alert
            alert(message);
        }
    }

    /**
     * Handle delete section action
     */
    async handleDeleteSection(sectionId) {
        const section = this.sections.get(sectionId);
        if (!section) return;
        
        const confirmed = confirm(`Are you sure you want to delete the section "${section.template_section?.name || 'Section'}"?`);
        if (!confirmed) return;
        
        try {
            await this.deleteSection(sectionId);
            
            // Emit custom event
            document.dispatchEvent(new CustomEvent('pagebuilder:section-deleted', {
                detail: { sectionId }
            }));
            
        } catch (error) {
            alert('Failed to delete section. Please try again.');
        }
    }

    /**
     * Get section by ID
     */
    getSection(sectionId) {
        return this.sections.get(sectionId);
    }

    /**
     * Get all sections
     */
    getAllSections() {
        return Array.from(this.sections.values());
    }

    /**
     * Get section DOM element
     */
    getSectionElement(sectionId) {
        return this.sectionElements.get(sectionId);
    }

    /**
     * Update section widget container after widgets are loaded
     */
    updateSectionWidgetContainer(sectionId, widgetsHtml) {
        const element = this.sectionElements.get(sectionId);
        if (element) {
            const container = element.querySelector(`[data-section-id="${sectionId}"].section-widgets-container`);
            if (container) {
                container.innerHTML = widgetsHtml || `
                    <div class="no-widgets text-center py-4">
                        <i class="ri-apps-line text-muted fs-1 mb-2"></i>
                        <p class="text-muted mb-0">No widgets in this section</p>
                        <small class="text-muted">Drag widgets from the sidebar</small>
                    </div>
                `;
            }
        }
    }

    // =====================================================================
    // HYBRID: RENDERED CONTENT METHODS (Live Preview Integration)
    // =====================================================================

    /**
     * Render section using pre-rendered HTML (Hybrid Approach)
     * This method works with rendered content from the API instead of building from scratch
     */
    renderSectionWithContent(section) {
        try {
            console.log('🎨 Rendering section with rendered content:', section.id, section.template_section?.name);
            
            // Store section data
            this.sections.set(section.id, section);
            
            // Create wrapper element for GridStack compatibility
            const sectionWrapper = this.createRenderedSectionWrapper(section);
            this.sectionElements.set(section.id, sectionWrapper);
            
            // Add to GridStack if available
            if (this.gridManager && this.gridManager.grid) {
                this.addRenderedSectionToGrid(section, sectionWrapper);
            } else {
                this.addRenderedSectionToContainer(sectionWrapper);
            }
            
            // Attach section events for editing
            this.attachRenderedSectionEvents(sectionWrapper, section);
            
            console.log('✅ Section rendered with content:', section.id);
            return sectionWrapper;
            
        } catch (error) {
            console.error('❌ Error rendering section with content:', error);
            throw error;
        }
    }

    /**
     * Create wrapper for rendered section content
     */
    createRenderedSectionWrapper(section) {
        const sectionName = section.template_section?.name || `Section ${section.id}`;
        const sectionType = section.template_section?.type || 'default';
        
        const wrapper = document.createElement('div');
        wrapper.className = 'grid-section-wrapper';
        wrapper.setAttribute('data-section-id', section.id);
        wrapper.setAttribute('data-section-type', sectionType);
        wrapper.id = `section-${section.id}`;
        
        wrapper.innerHTML = `
            <div class="section-controls d-flex justify-content-between align-items-center p-2 bg-light border-bottom">
                <div class="section-info">
                    <span class="section-name fw-bold">${sectionName}</span>
                    <span class="widget-count text-muted ms-2">${section.widgets?.length || 0} widgets</span>
                </div>
                <div class="section-actions">
                    <button class="btn btn-sm btn-outline-primary me-1" onclick="pageBuilder.editSection(${section.id})" title="Edit Section">
                        <i class="ri-edit-line"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-secondary me-1" onclick="pageBuilder.addWidgetToSection(${section.id})" title="Add Widget">
                        <i class="ri-add-line"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="pageBuilder.deleteSection(${section.id})" title="Delete Section">
                        <i class="ri-delete-line"></i>
                    </button>
                </div>
            </div>
            <div class="section-content">
                ${section.rendered_html || '<div class="empty-section p-4 text-center text-muted">No content</div>'}
            </div>
        `;
        
        return wrapper;
    }

    /**
     * Add rendered section to GridStack
     */
    addRenderedSectionToGrid(section, sectionWrapper) {
        if (!this.gridManager || !this.gridManager.grid) {
            console.warn('⚠️ GridStack not available for section:', section.id);
            return;
        }

        const gridOptions = {
            x: section.grid_position?.x || 0,
            y: section.grid_position?.y || 0,
            w: section.grid_position?.w || 12,
            h: section.grid_position?.h || 4,
            id: `section-${section.id}`,
            content: sectionWrapper.outerHTML
        };

        try {
            this.gridManager.grid.addWidget(gridOptions);
            console.log('✅ Section added to GridStack:', section.id);
        } catch (error) {
            console.error('❌ Failed to add section to GridStack:', error);
            this.addRenderedSectionToContainer(sectionWrapper);
        }
    }

    /**
     * Add rendered section to container (fallback)
     */
    addRenderedSectionToContainer(sectionWrapper) {
        const container = document.getElementById('gridStackContainer') || 
                         document.querySelector('.grid-stack') ||
                         document.querySelector('#canvasArea');
        
        if (container) {
            container.appendChild(sectionWrapper);
            console.log('✅ Section added to container (fallback)');
        } else {
            console.error('❌ No container found for section');
        }
    }

    /**
     * Attach events to rendered section
     */
    attachRenderedSectionEvents(sectionWrapper, section) {
        // Double-click to edit
        sectionWrapper.addEventListener('dblclick', () => {
            this.editSection(section.id);
        });

        // Context menu for advanced options
        sectionWrapper.addEventListener('contextmenu', (e) => {
            e.preventDefault();
            this.showSectionContextMenu(e, section.id);
        });

        // Hover effects
        sectionWrapper.addEventListener('mouseenter', () => {
            sectionWrapper.classList.add('section-hover');
        });

        sectionWrapper.addEventListener('mouseleave', () => {
            sectionWrapper.classList.remove('section-hover');
        });
    }

    /**
     * Edit section (trigger modal or inline editing)
     */
    editSection(sectionId) {
        console.log('✏️ Edit section:', sectionId);
        
        // Dispatch event for main controller to handle
        document.dispatchEvent(new CustomEvent('pagebuilder:edit-section', {
            detail: { sectionId }
        }));
    }

    /**
     * Show context menu for section
     */
    showSectionContextMenu(event, sectionId) {
        console.log('📋 Show context menu for section:', sectionId);
        // TODO: Implement context menu
    }

    /**
     * Refresh rendered section content
     */
    async refreshSectionContent(sectionId) {
        try {
            console.log('🔄 Refreshing section content:', sectionId);
            
            const response = await this.api.getRenderedSection(sectionId);
            
            if (response.success && response.data) {
                const updatedSection = response.data;
                
                // Update stored section
                this.sections.set(sectionId, updatedSection);
                
                // Update DOM element
                const sectionWrapper = this.sectionElements.get(sectionId);
                if (sectionWrapper) {
                    const contentDiv = sectionWrapper.querySelector('.section-content');
                    if (contentDiv) {
                        contentDiv.innerHTML = updatedSection.rendered_html || '<div class="empty-section p-4 text-center text-muted">No content</div>';
                    }
                }
                
                console.log('✅ Section content refreshed:', sectionId);
                return updatedSection;
            }
            
        } catch (error) {
            console.error('❌ Error refreshing section content:', error);
            throw error;
        }
    }

    /**
     * Reload the preview iframe to show updated changes
     */
    async reloadPreview() {
        try {
            console.log('🔄 Reloading preview iframe...');
            
            // Find the preview iframe
            const previewIframe = document.getElementById('pagePreviewIframe');
            if (previewIframe) {
                // Store current src to force reload
                const currentSrc = previewIframe.src;
                
                // Add timestamp to force reload
                const separator = currentSrc.includes('?') ? '&' : '?';
                const newSrc = `${currentSrc}${separator}_t=${Date.now()}`;
                
                // Reload iframe
                previewIframe.src = newSrc;
                
                console.log('✅ Preview iframe reloaded');
            } else {
                console.warn('⚠️ Preview iframe not found');
            }
            
        } catch (error) {
            console.error('❌ Error reloading preview:', error);
        }
    }
}

// Export for global use
window.SectionManager = SectionManager;

console.log('📦 Section Manager module loaded');