/**
 * Component Manager - Placeholder for testing UI
 */

class ComponentManager {
    constructor(editor, api) {
        this.editor = editor;
        this.api = api;
        
        console.log('🧩 ComponentManager initialized (placeholder)');
    }
    
    destroy() {
        console.log('🗑️ ComponentManager destroyed');
    }
}

window.ComponentManager = ComponentManager;
