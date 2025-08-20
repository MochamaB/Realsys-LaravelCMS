/**
 * Canvas Manager - Placeholder for testing UI
 */

class CanvasManager {
    constructor(editor, api) {
        this.editor = editor;
        this.api = api;
        
        console.log('🎨 CanvasManager initialized (placeholder)');
    }
    
    destroy() {
        console.log('🗑️ CanvasManager destroyed');
    }
}

window.CanvasManager = CanvasManager;
