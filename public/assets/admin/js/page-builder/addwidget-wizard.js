/**
 * Add Widget Wizard
 *
 * Step-by-step wizard interface for adding widgets to sections
 * Steps: 1) Widget Selection 2) Content Type Selection 3) Content Items Selection
 */
class AddWidgetWizard {
    constructor() {
        this.currentStep = 1;
        this.totalSteps = 3;

        this.selectedWidget = null;
        this.selectedContentType = null;
        this.selectedItems = [];
        this.targetSectionData = null;

        this.themeWidgets = [];
        this.defaultWidgets = [];

        // DOM elements
        this.modal = null;
        this.nextButton = null;
        this.backButton = null;
        this.addWidgetButton = null;
        this.stepInfo = null;

        console.log('🧩 Add Widget Wizard initialized');
    }

    /**
     * Initialize the wizard
     */
    init() {
        // Get DOM elements
        this.modal = document.getElementById('addWidgetModal');
        this.nextButton = document.getElementById('nextButton');
        this.backButton = document.getElementById('backButton');
        this.addWidgetButton = document.getElementById('addWidgetButton');
        this.stepInfo = document.getElementById('stepInfo');

        if (!this.modal) {
            console.error('❌ Add Widget Modal not found');
            return;
        }

        this.setupEventListeners();
        this.updateUI();

        console.log('✅ Add Widget Wizard ready');
    }

    /**
     * Set widget data from server (not needed anymore - data is in HTML)
     */
    setWidgetData(themeWidgets, defaultWidgets) {
        console.log('🧩 Wizard data passed (using HTML instead of JS)');
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Widget selection in step 1
        document.addEventListener('click', (e) => {
            if (!e.target || !e.target.closest) return;
            if (!e.target.closest('#addWidgetModal')) return;

            const widgetCard = e.target.closest('.widget-card');
            if (widgetCard && this.currentStep === 1) {
                this.selectWidget(widgetCard);
                return;
            }

            // Content type selection in step 2
            const contentTypeCard = e.target.closest('.content-type-card');
            if (contentTypeCard && this.currentStep === 2) {
                this.selectContentType(contentTypeCard);
                return;
            }

            // Content item checkbox in step 3
            if (e.target.classList.contains('content-item-checkbox') && this.currentStep === 3) {
                this.toggleContentItem(e.target);
                return;
            }
        });

        // Navigation buttons
        this.nextButton?.addEventListener('click', () => this.nextStep());
        this.backButton?.addEventListener('click', () => this.previousStep());
        this.addWidgetButton?.addEventListener('click', () => this.addWidget());

        // Modal reset on close
        this.modal?.addEventListener('hidden.bs.modal', () => this.resetWizard());

        console.log('✅ Wizard event listeners attached');
    }

    /**
     * Select a widget in step 1
     */
    selectWidget(widgetCard) {
        // Clear previous selection
        document.querySelectorAll('.widget-card').forEach(card => {
            card.classList.remove('selected');
        });

        // Apply selection
        widgetCard.classList.add('selected');

        // Store selection
        this.selectedWidget = {
            id: widgetCard.dataset.widgetId,
            name: widgetCard.dataset.widgetName,
            type: widgetCard.dataset.widgetType,
            hasContentTypes: widgetCard.dataset.hasContentTypes === 'true'
        };

        // Update UI
        this.showSelectedWidget();
        this.updateNavigationButtons();

        console.log('🎯 Widget selected:', this.selectedWidget);
    }

    /**
     * Show selected widget info
     */
    showSelectedWidget() {
        const alert = document.getElementById('selectedWidgetAlert');
        const nameSpan = document.getElementById('selectedWidgetName');
        const typeSpan = document.getElementById('selectedWidgetType');

        if (alert && nameSpan && typeSpan) {
            nameSpan.textContent = this.selectedWidget.name;
            typeSpan.textContent = this.selectedWidget.type;
            typeSpan.className = `badge ms-2 ${this.selectedWidget.type === 'theme' ? 'bg-primary' : 'bg-success'}`;
            alert.style.display = 'block';
        }
    }

    /**
     * Select a content type in step 2
     */
    selectContentType(contentTypeCard) {
        // Clear previous selection
        document.querySelectorAll('.content-type-card').forEach(card => {
            card.classList.remove('selected');
        });

        // Apply selection
        contentTypeCard.classList.add('selected');

        // Store selection
        this.selectedContentType = {
            id: contentTypeCard.dataset.contentTypeId,
            name: contentTypeCard.querySelector('.content-type-name')?.textContent || 'Content Type'
        };

        // Update UI
        this.updateNavigationButtons();

        console.log('📋 Content type selected:', this.selectedContentType);
    }

    /**
     * Toggle content item selection in step 3
     */
    toggleContentItem(checkbox) {
        const itemCard = checkbox.closest('.content-item-card');
        const itemId = itemCard?.dataset.contentItemId;

        if (!itemId) return;

        if (checkbox.checked) {
            // Add to selection
            if (!this.selectedItems.find(item => item.id === itemId)) {
                this.selectedItems.push({
                    id: itemId,
                    title: itemCard.querySelector('.content-item-title')?.textContent || 'Untitled'
                });
            }
            itemCard.classList.add('selected');
        } else {
            // Remove from selection
            this.selectedItems = this.selectedItems.filter(item => item.id !== itemId);
            itemCard.classList.remove('selected');
        }

        this.updateSelectedItemsCount();
        this.updateNavigationButtons();

        console.log('📄 Content items selected:', this.selectedItems.length);
    }

    /**
     * Update selected items count
     */
    updateSelectedItemsCount() {
        const alert = document.getElementById('selectedItemsAlert');
        const countSpan = document.getElementById('selectedItemsCount');

        if (countSpan) {
            countSpan.textContent = this.selectedItems.length;
        }

        if (alert) {
            alert.style.display = this.selectedItems.length > 0 ? 'block' : 'none';
        }
    }

    /**
     * Move to next step
     */
    async nextStep() {
        if (this.currentStep >= this.totalSteps) return;

        // Handle step-specific logic
        if (this.currentStep === 1) {
            // Moving from widgets to content types
            if (this.selectedWidget.hasContentTypes) {
                this.showContentTypesForWidget();
            } else {
                // Skip step 2 and 3 for static widgets
                this.currentStep = this.totalSteps;
                this.updateUI();
                this.updateNavigationButtons();
                return;
            }
        } else if (this.currentStep === 2) {
            // Moving from content types to content items
            this.showContentItemsForContentType();
        }

        this.currentStep++;
        this.updateUI();
        this.updateNavigationButtons();
    }

    /**
     * Move to previous step
     */
    previousStep() {
        if (this.currentStep <= 1) return;

        this.currentStep--;
        this.updateUI();
        this.updateNavigationButtons();
    }

    /**
     * Show content types for selected widget (using preloaded HTML)
     */
    showContentTypesForWidget() {
        console.log('🔍 Showing content types for widget ID:', this.selectedWidget.id);

        // Hide all content type cards
        const allContentTypeCards = document.querySelectorAll('.modal-content-type-card');
        allContentTypeCards.forEach(card => {
            card.style.display = 'none';
        });

        // Show content types for this widget
        const widgetContentTypes = document.querySelectorAll(`.modal-content-type-card[data-widget-id="${this.selectedWidget.id}"]`);
        widgetContentTypes.forEach(card => {
            card.style.display = 'block';
        });

        // Update step 2 widget name
        const widgetNameSpan = document.getElementById('step2-widget-name');
        if (widgetNameSpan) {
            widgetNameSpan.textContent = this.selectedWidget.name;
        }

        console.log(`✅ Showing ${widgetContentTypes.length} content types for widget`);
    }


    /**
     * Show content items for selected content type (using preloaded HTML)
     */
    showContentItemsForContentType() {
        console.log('🔍 Showing content items for content type ID:', this.selectedContentType.id);

        // Hide all content item cards
        const allContentItemCards = document.querySelectorAll('.modal-content-item-card');
        allContentItemCards.forEach(card => {
            card.style.display = 'none';
        });

        // Show content items for this content type
        const contentTypeItems = document.querySelectorAll(`.modal-content-item-card[data-content-type-id="${this.selectedContentType.id}"]`);
        contentTypeItems.forEach(card => {
            card.style.display = 'block';
        });

        // Update step 3 content type name
        const contentTypeNameSpan = document.getElementById('step3-content-type-name');
        if (contentTypeNameSpan) {
            contentTypeNameSpan.textContent = this.selectedContentType.name;
        }

        // Reset selected items
        this.selectedItems = [];
        this.updateSelectedItemsCount();

        // Clear all checkboxes
        contentTypeItems.forEach(card => {
            const checkbox = card.querySelector('.content-item-checkbox');
            if (checkbox) {
                checkbox.checked = false;
            }
            card.classList.remove('selected');
        });

        console.log(`✅ Showing ${contentTypeItems.length} content items for content type`);
    }


    /**
     * Update UI for current step
     */
    updateUI() {
        // Hide all steps
        document.querySelectorAll('.wizard-step').forEach(step => {
            step.style.display = 'none';
        });

        // Show current step
        const currentStepElement = document.getElementById(`step${this.currentStep}-${this.getStepName()}`);
        if (currentStepElement) {
            currentStepElement.style.display = 'block';
        }

        // Update step indicators
        for (let i = 1; i <= this.totalSteps; i++) {
            const indicator = document.getElementById(`step${i}-indicator`);
            if (indicator) {
                indicator.classList.remove('active', 'completed');
                if (i < this.currentStep) {
                    indicator.classList.add('completed');
                } else if (i === this.currentStep) {
                    indicator.classList.add('active');
                }
            }
        }

        // Update step dividers
        document.querySelectorAll('.step-divider').forEach((divider, index) => {
            divider.classList.toggle('completed', index < this.currentStep - 1);
        });

        // Update step info
        this.updateStepInfo();
    }

    /**
     * Get step name by number
     */
    getStepName() {
        const names = ['widgets', 'content-types', 'content-items'];
        return names[this.currentStep - 1] || 'widgets';
    }

    /**
     * Update step info text
     */
    updateStepInfo() {
        if (!this.stepInfo) return;

        const messages = {
            1: 'Select a widget to continue',
            2: 'Choose a content type for your widget',
            3: 'Select content items to include'
        };

        this.stepInfo.textContent = messages[this.currentStep] || '';
    }

    /**
     * Update navigation buttons
     */
    updateNavigationButtons() {
        if (!this.nextButton || !this.backButton || !this.addWidgetButton) return;

        // Back button
        this.backButton.style.display = this.currentStep > 1 ? 'inline-block' : 'none';

        // Next/Add button logic
        let canProceed = false;

        if (this.currentStep === 1) {
            canProceed = this.selectedWidget !== null;
        } else if (this.currentStep === 2) {
            canProceed = this.selectedContentType !== null;
        } else if (this.currentStep === 3) {
            canProceed = this.selectedItems.length > 0;
        }

        // Show appropriate button
        if (this.currentStep === this.totalSteps) {
            this.nextButton.style.display = 'none';
            this.addWidgetButton.style.display = 'inline-block';
            this.addWidgetButton.disabled = !canProceed;
        } else {
            this.nextButton.style.display = 'inline-block';
            this.nextButton.disabled = !canProceed;
            this.addWidgetButton.style.display = 'none';
        }
    }


    /**
     * Add widget to section
     */
    addWidget() {
        if (!this.targetSectionData) {
            console.error('❌ No target section data');
            return;
        }

        const widgetData = {
            widget: this.selectedWidget,
            contentType: this.selectedContentType,
            items: this.selectedItems,
            section: this.targetSectionData
        };

        console.log('✅ Adding widget:', widgetData);

        // TODO: Replace with actual API call
        alert(`Widget "${this.selectedWidget.name}" will be added to section ${this.targetSectionData.id}!\n\nContent Type: ${this.selectedContentType?.name || 'None'}\nSelected Items: ${this.selectedItems.length}\n\nAPI integration coming in next phase.`);

        // Close modal
        bootstrap.Modal.getInstance(this.modal).hide();
    }

    /**
     * Reset wizard to initial state
     */
    resetWizard() {
        this.currentStep = 1;
        this.selectedWidget = null;
        this.selectedContentType = null;
        this.selectedItems = [];
        this.targetSectionData = null;

        // Clear UI selections
        document.querySelectorAll('.widget-card, .content-type-card, .content-item-card').forEach(card => {
            card.classList.remove('selected');
        });

        document.querySelectorAll('.content-item-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });

        // Hide alerts
        document.getElementById('selectedWidgetAlert').style.display = 'none';
        document.getElementById('selectedItemsAlert').style.display = 'none';

        // Reset to step 1
        this.updateUI();
        this.updateNavigationButtons();

        console.log('🔄 Wizard reset');
    }

    /**
     * Set section context (called by parent communicator)
     */
    setAddWidgetContext(sectionData) {
        this.targetSectionData = sectionData;

        const targetInfo = document.getElementById('targetSectionInfo');
        if (targetInfo) {
            targetInfo.textContent = `Adding widget to: Section ${sectionData.id}`;
        }

        console.log('🎯 Add widget context set for section:', sectionData.id);
    }
}

// Global instance
window.addWidgetWizard = null;

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('🧩 addwidget-wizard.js DOMContentLoaded fired');

    const modalElement = document.getElementById('addWidgetModal');
    if (modalElement) {
        window.addWidgetWizard = new AddWidgetWizard();
        window.addWidgetWizard.init();

        // Set up global method for parent communicator
        window.setAddWidgetContext = function(sectionData) {
            if (window.addWidgetWizard) {
                window.addWidgetWizard.setAddWidgetContext(sectionData);
            }
        };

        console.log('✅ Add Widget Wizard initialized');
    } else {
        console.log('🚫 Add Widget Wizard not initialized - addWidgetModal not found');
    }
});

console.log('📦 Add Widget Wizard module loaded');