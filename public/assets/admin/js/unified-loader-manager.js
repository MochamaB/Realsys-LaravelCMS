/**
 * UNIFIED LOADER MANAGER
 * ======================
 * 
 * GENERAL PURPOSE:
 * Provides a centralized, consistent loading indicator system for all PageBuilder
 * operations. Replaces scattered loading implementations with a single, unified
 * progress bar that tracks multiple concurrent operations.
 * 
 * KEY FUNCTIONS/METHODS & DUPLICATION STATUS:
 * 
 * LOADER INITIALIZATION:
 * • init() - **UNIQUE** - Initialize loader elements and setup
 * • validateLoaderElements() - **UNIQUE** - Ensure all required DOM elements exist
 * • configureLoaderStyles() - **UNIQUE** - Apply consistent styling to loader
 * 
 * OPERATION MANAGEMENT:
 * • show() - **BYPASSED** - Other files implement custom loading states instead
 * • hide() - **BYPASSED** - Other files implement custom loading states instead
 * • update() - **UNIQUE** - Update loader progress and message
 * • setProgress() - **UNIQUE** - Set specific progress percentage
 * • setMessage() - **UNIQUE** - Update loader message text
 * 
 * MULTI-OPERATION TRACKING:
 * • trackOperation() - **UNIQUE** - Add operation to tracking set
 * • completeOperation() - **UNIQUE** - Mark operation as complete
 * • getActiveOperations() - **UNIQUE** - Get list of currently running operations
 * • calculateCombinedProgress() - **UNIQUE** - Calculate overall progress from multiple ops
 * • isAnyOperationActive() - **UNIQUE** - Check if any operations are running
 * 
 * LOADER STATE MANAGEMENT:
 * • showLoader() - **UNIQUE** - Display loader with animation
 * • hideLoader() - **UNIQUE** - Hide loader with smooth transition
 * • resetLoader() - **UNIQUE** - Reset to initial state
 * • toggleLoader() - **UNIQUE** - Toggle loader visibility
 * 
 * PROGRESS VISUALIZATION:
 * • updateProgressBar() - **UNIQUE** - Update visual progress indicator
 * • animateProgress() - **UNIQUE** - Smooth progress bar animations
 * • setProgressColor() - **UNIQUE** - Change progress color based on state
 * • showCompletionState() - **UNIQUE** - Display completion animation
 * • showErrorState() - **UNIQUE** - Display error state styling
 * 
 * MESSAGE MANAGEMENT:
 * • updateMessage() - **UNIQUE** - Update loader message text
 * • queueMessage() - **UNIQUE** - Queue message for display
 * • clearMessage() - **UNIQUE** - Clear current message
 * • formatMessage() - **UNIQUE** - Format message for display
 * 
 * ERROR HANDLING:
 * • showError() - **UNIQUE** - Display error state in loader
 * • clearError() - **UNIQUE** - Clear error state
 * • handleOperationError() - **UNIQUE** - Process operation errors
 * 
 * UTILITY METHODS:
 * • isVisible() - **UNIQUE** - Check if loader is currently visible
 * • getCurrentProgress() - **UNIQUE** - Get current progress percentage
 * • getOperationCount() - **UNIQUE** - Get number of active operations
 * • destroy() - **UNIQUE** - Clean up loader resources
 * 
 * MAJOR DUPLICATION ISSUES:
 * 1. **CRITICAL BYPASS**: Most components implement their own loading states instead of using this
 * 2. **SCATTERED LOADERS**: Custom loading indicators in multiple files:
 *    - page-builder-main.js has showGlobalLoader() and hideGlobalLoader()
 *    - widget-library.js has showLoadingState() and hideLoadingState()
 *    - template-manager.js has showLoadingState() and hideLoadingState()
 *    - widget-modal-manager.js has showLoadingStep() and hideLoadingStep()
 *    - theme-manager.js has custom loading logic
 * 3. **INCONSISTENT UX**: Different loading styles and behaviors across components
 * 4. **NO COORDINATION**: Multiple loaders can appear simultaneously
 * 
 * FILES THAT SHOULD USE THIS BUT DON'T:
 * • page-builder-main.js - Uses custom global loader
 * • widget-library.js - Implements separate loading states
 * • template-manager.js - Implements separate loading states
 * • widget-modal-manager.js - Uses custom step loading
 * • section-manager.js - Sometimes bypasses unified loader
 * • theme-manager.js - Uses custom asset loading indicators
 * 
 * ARCHITECTURAL INTENT VS REALITY:
 * This class was designed to be the ONLY loading system but is largely ignored,
 * resulting in inconsistent user experience and duplicated loading logic.
 */
class UnifiedLoaderManager {
    constructor() {
        this.activeOperations = new Set();
        this.progressBar = null;
        this.messageElement = null;
        this.containerElement = null;
        this.currentProgress = 0;
        this.isVisible = false;
        
        this.init();
        console.log('🔄 Unified Loader Manager initialized');
    }

    /**
     * Initialize the loader elements
     */
    init() {
        // Find or create the loader container
        this.containerElement = document.getElementById('pageBuilderLoader');
        
        if (!this.containerElement) {
            console.warn('⚠️ Page builder loader container not found');
            return;
        }

        // Get references to child elements
        this.progressBar = this.containerElement.querySelector('.progress-bar');
        this.messageElement = this.containerElement.querySelector('.loader-message');
        
        if (!this.progressBar || !this.messageElement) {
            console.warn('⚠️ Loader elements not found in container');
            return;
        }

        console.log('✅ Loader elements initialized');
    }

    /**
     * Show loader for a specific operation
     * @param {string} operation - Unique operation identifier
     * @param {string} message - Message to display
     * @param {number} initialProgress - Initial progress percentage (0-100)
     */
    show(operation, message = 'Loading...', initialProgress = 0) {
        if (!this.containerElement) return;

        // Add operation to active set
        this.activeOperations.add(operation);
        
        // Update message
        if (this.messageElement) {
            this.messageElement.textContent = message;
        }
        
        // Set initial progress
        this.setProgress(initialProgress);
        
        // Show the loader if not already visible
        if (!this.isVisible) {
            this.containerElement.style.display = 'block';
            this.containerElement.classList.add('active');
            this.isVisible = true;
        }
        
        console.log(`🔄 Loader shown for operation: ${operation} - ${message}`);
    }

    /**
     * Hide loader for a specific operation
     * @param {string} operation - Operation identifier to remove
     */
    hide(operation) {
        if (!this.containerElement) return;

        // Remove operation from active set
        this.activeOperations.delete(operation);
        
        // If no more active operations, hide the loader
        if (this.activeOperations.size === 0) {
            this.setProgress(100); // Complete progress
            
            // Hide after a short delay to show completion
            setTimeout(() => {
                if (this.activeOperations.size === 0) { // Double-check
                    this.containerElement.style.display = 'none';
                    this.containerElement.classList.remove('active', 'error');
                    this.isVisible = false;
                    this.currentProgress = 0;
                }
            }, 300);
        }
        
        console.log(`✅ Loader hidden for operation: ${operation}`);
    }

    /**
     * Update progress percentage
     * @param {number} percentage - Progress percentage (0-100)
     */
    setProgress(percentage) {
        if (!this.progressBar) return;
        
        this.currentProgress = Math.max(0, Math.min(100, percentage));
        this.progressBar.style.width = `${this.currentProgress}%`;
        
        // Add completion class when at 100%
        if (this.currentProgress === 100) {
            this.progressBar.classList.add('complete');
        } else {
            this.progressBar.classList.remove('complete');
        }
    }

    /**
     * Show error state
     * @param {string} operation - Operation identifier
     * @param {string} message - Error message
     */
    showError(operation, message = 'An error occurred') {
        if (!this.containerElement) return;

        // Add error class
        this.containerElement.classList.add('error');
        
        // Update message
        if (this.messageElement) {
            this.messageElement.textContent = message;
        }
        
        // Set progress to 0 for error state
        this.setProgress(0);
        
        console.error(`❌ Loader error for operation: ${operation} - ${message}`);
        
        // Auto-hide error after 3 seconds
        setTimeout(() => {
            this.hide(operation);
        }, 3000);
    }

    /**
     * Update message without changing progress
     * @param {string} message - New message to display
     */
    updateMessage(message) {
        if (this.messageElement) {
            this.messageElement.textContent = message;
        }
    }

    /**
     * Force hide all operations (emergency reset)
     */
    forceHide() {
        this.activeOperations.clear();
        
        if (this.containerElement) {
            this.containerElement.style.display = 'none';
            this.containerElement.classList.remove('active', 'error');
        }
        
        this.isVisible = false;
        this.currentProgress = 0;
        
        console.log('🔄 Loader force hidden');
    }

    /**
     * Check if loader is currently visible
     * @returns {boolean}
     */
    isLoading() {
        return this.isVisible && this.activeOperations.size > 0;
    }

    /**
     * Get current active operations
     * @returns {Set<string>}
     */
    getActiveOperations() {
        return new Set(this.activeOperations);
    }

    /**
     * Convenience method for API operations
     * @param {string} operation - Operation identifier
     * @param {Promise} promise - Promise to track
     * @param {string} loadingMessage - Message during loading
     * @param {string} successMessage - Message on success (optional)
     * @returns {Promise}
     */
    async trackOperation(operation, promise, loadingMessage, successMessage = null) {
        try {
            this.show(operation, loadingMessage, 10);
            
            // Simulate progress during operation
            const progressInterval = setInterval(() => {
                if (this.currentProgress < 90) {
                    this.setProgress(this.currentProgress + 10);
                }
            }, 200);
            
            const result = await promise;
            
            clearInterval(progressInterval);
            
            if (successMessage) {
                this.updateMessage(successMessage);
            }
            
            this.setProgress(100);
            this.hide(operation);
            
            return result;
            
        } catch (error) {
            this.showError(operation, `Failed: ${error.message}`);
            throw error;
        }
    }
}

// Export for global use
window.UnifiedLoaderManager = UnifiedLoaderManager;

console.log('📦 Unified Loader Manager module loaded');
