/**
 * Section Manager
 * 
 * Handles all section-related operations including CRUD, positioning,
 * and rendering sections in the GridStack layout.
 */
class SectionManager {
    constructor(api, gridManager) {
        this.api = api;
        this.gridManager = gridManager;
        this.sections = new Map(); // Store sections by ID
        this.sectionElements = new Map(); // Store DOM elements by section ID
        
        console.log('📋 Section Manager initialized');
    }

    /**
     * Load all sections for the current page
     */
    async loadSections() {
        try {
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
                return response.data;
            } else {
                console.warn('⚠️ No sections found in response');
                return [];
            }
        } catch (error) {
            console.error('❌ Error loading sections:', error);
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
     * Add section to proper layout area based on section type
     */
    addSectionToLayout(container, sectionElement, section) {
        const sectionType = section.template_section?.section_type || 'content';
        
        // Create or get layout structure
        let layoutStructure = container.querySelector('.layout-structure');
        if (!layoutStructure) {
            layoutStructure = this.createLayoutStructure(container);
        }
        
        // Add section to appropriate area based on type
        switch (sectionType) {
            case 'header':
                const header = layoutStructure.querySelector('.site-header .header-sections');
                header.appendChild(sectionElement);
                console.log('📍 Added header section to header area');
                break;
                
            case 'footer':
                const footer = layoutStructure.querySelector('.site-footer .footer-sections');
                footer.appendChild(sectionElement);
                console.log('📍 Added footer section to footer area');
                break;
                
            default:
                // Content sections go in main area
                const main = layoutStructure.querySelector('.site-main .page-content .content-sections');
                main.appendChild(sectionElement);
                console.log('📍 Added content section to main area');
        }
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
        
        // Emit custom event for other components to handle
        document.dispatchEvent(new CustomEvent('pagebuilder:edit-section', {
            detail: { sectionId, section: this.sections.get(sectionId) }
        }));
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
}

// Export for global use
window.SectionManager = SectionManager;

console.log('📦 Section Manager module loaded');