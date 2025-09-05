/**
 * Update Manager
 * 
 * Handles debounced updates to prevent spam and manage API calls
 */
class UpdateManager {
    constructor(apiUrl, csrfToken) {
        this.apiUrl = apiUrl;
        this.csrfToken = csrfToken;
        this.updateQueue = new Map();
        this.pendingUpdates = new Set();
        this.debounceTime = 800; // 800ms debounce
    }
    
    /**
     * Queue an update with debouncing
     */
    queueUpdate(type, id, data, callback = null) {
        const key = `${type}_${id}`;
        
        // Cancel previous timer for this item
        if (this.updateQueue.has(key)) {
            clearTimeout(this.updateQueue.get(key).timer);
        }
        
        // Queue new update
        const timer = setTimeout(async () => {
            await this.executeUpdate(type, id, data, callback);
        }, this.debounceTime);
        
        this.updateQueue.set(key, { timer, data, callback });
        
        console.log(`⏳ Queued update: ${type} ${id}`);
    }
    
    /**
     * Execute the actual update
     */
    async executeUpdate(type, id, data, callback = null) {
        const key = `${type}_${id}`;
        this.pendingUpdates.add(key);
        
        try {
            console.log(`🔄 Executing update: ${type} ${id}`);
            
            const response = await this.sendUpdate(type, id, data);
            
            if (response.success) {
                console.log(`✅ Update successful: ${type} ${id}`);
                
                // Execute callback if provided
                if (callback && typeof callback === 'function') {
                    await callback(response);
                }
                
                // Reload preview if requested
                if (response.data && response.data.reload_preview) {
                    await this.reloadPreview();
                }
                
                // Show success message
                this.showMessage('Changes saved automatically', 'success');
            } else {
                throw new Error(response.error || 'Update failed');
            }
            
        } catch (error) {
            console.error(`❌ Update failed: ${type} ${id}`, error);
            this.showMessage('Failed to save changes: ' + error.message, 'error');
        } finally {
            this.pendingUpdates.delete(key);
            this.updateQueue.delete(key);
        }
    }
    
    /**
     * Send update to backend
     */
    async sendUpdate(type, id, data) {
        let url, method = 'POST';
        
        switch (type) {
            case 'widget':
                url = `${this.apiUrl}/widgets/${id}/update`;
                break;
            case 'section':
                url = `${this.apiUrl}/sections/${id}/update`;
                break;
            default:
                throw new Error(`Unknown update type: ${type}`);
        }
        
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': this.csrfToken
            },
            body: JSON.stringify(data)
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        return await response.json();
    }
    
    /**
     * Check if update is pending for a specific item
     */
    isPending(type, id) {
        return this.pendingUpdates.has(`${type}_${id}`);
    }
    
    /**
     * Get pending updates count
     */
    getPendingCount() {
        return this.pendingUpdates.size;
    }
    
    /**
     * Cancel all pending updates
     */
    cancelAll() {
        this.updateQueue.forEach(({ timer }) => clearTimeout(timer));
        this.updateQueue.clear();
        this.pendingUpdates.clear();
        console.log('🚫 All pending updates cancelled');
    }
    
    /**
     * Reload preview iframe
     */
    async reloadPreview() {
        if (window.livePreview) {
            await window.livePreview.reloadPreview();
        }
    }
    
    /**
     * Show message to user
     */
    showMessage(message, type = 'info') {
        if (window.livePreview) {
            window.livePreview.showMessage(message, type);
        } else {
            console.log(`${type.toUpperCase()}: ${message}`);
        }
    }
}

// Export for global use
window.UpdateManager = UpdateManager;