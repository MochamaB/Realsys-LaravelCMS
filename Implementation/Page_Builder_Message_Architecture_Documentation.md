# Page Builder Message Architecture Documentation

## Overview

The Page Builder uses a scalable message routing architecture to handle communication between the iframe preview and the parent window. This system ensures clean separation of concerns and easy extensibility.

## Architecture Components

### 1. Message Flow Direction

```
Iframe Preview → Central Message Sender → Parent Message Router → Action Handlers
```

### 2. File Structure

```
js/page-builder/
├── pagebuilder-preview-helper.js     # Central message sender (iframe side)
├── page-builder.js                   # Message router & coordinator (parent side)
├── pagebuilder-page-preview.js       # Page interactions (iframe side)
├── actions/
│   ├── pagebuilder-section-actions.js    # Section action handlers (parent)
│   ├── pagebuilder-page-actions.js       # Page action handlers (parent)
│   └── pagebuilder-widget-actions.js     # Widget action handlers (parent)
└── preview/
    ├── pagebuilder-page-preview.js       # Page preview logic (iframe)
    ├── pagebuilder-section-preview.js    # Section preview logic (iframe)
    └── pagebuilder-widget-preview.js     # Widget preview logic (iframe)
```

## Core Components

### 1. Central Message Sender (iframe side)
**File**: `pagebuilder-preview-helper.js`

```javascript
window.pageBuilderMessageSender = {
    sendToParent: function(actionType, data, options) {
        // Standardized message format
        const message = {
            type: actionType,
            source: 'pagebuilder-iframe',
            timestamp: Date.now(),
            data: data,
            options: options
        };
        window.parent.postMessage(message, '*');
    },

    // Organized by domain
    page: {
        editRequested: function(pageData) { /* ... */ },
        addSectionRequested: function(pageData) { /* ... */ }
    },
    section: {
        editRequested: function(sectionData) { /* ... */ },
        deleteRequested: function(sectionData) { /* ... */ }
    },
    widget: {
        addRequested: function(widgetData) { /* ... */ }
    }
};
```

### 2. Message Router (parent side)
**File**: `page-builder.js`

```javascript
class PageBuilderMessageRouter {
    constructor() {
        this.actionHandlers = new Map();
        this.messageHistory = [];
        this.setupMessageListener();
    }

    registerHandler(actionType, handler) {
        this.actionHandlers.set(actionType, handler);
    }

    handleMessage(event) {
        const { type, data } = event.data;
        const handler = this.actionHandlers.get(type);
        if (handler) handler(data, event);
    }
}
```

### 3. Action Handlers (parent side)
**Files**: `actions/pagebuilder-*-actions.js`

```javascript
class PageBuilderSectionActions {
    handleAddSection(data, event) {
        // Open section templates modal
        // Handle section-specific logic
    }
    
    handleSectionEdit(data, event) {
        // Open section editor
    }
}
```

## Message Types

### Page Actions
- `page-edit-requested` - Edit page settings
- `page-selected` - Page selection changed
- `page-deselected` - Page selection cleared
- `add-section-requested` - Add new section to page

### Section Actions
- `section-edit-requested` - Edit section settings
- `section-delete-requested` - Delete section
- `section-move-requested` - Reorder sections
- `section-selected` - Section selection changed

### Widget Actions
- `add-widget-requested` - Add widget to section
- `widget-edit-requested` - Edit widget settings
- `widget-delete-requested` - Delete widget
- `widget-selected` - Widget selection changed

## How to Add New Actions

### Step 1: Define the Action in Central Message Sender (iframe side)

**File**: `pagebuilder-preview-helper.js`

```javascript
// Add to appropriate domain object
window.pageBuilderMessageSender = {
    // ... existing code ...
    
    widget: {
        // ... existing methods ...
        
        // NEW ACTION
        duplicateRequested: function(widgetData) {
            window.pageBuilderMessageSender.sendToParent('widget-duplicate-requested', widgetData);
        }
    }
};
```

### Step 2: Use the Action in Preview Logic (iframe side)

**File**: `pagebuilder-widget-preview.js` (or appropriate preview file)

```javascript
// In your event handler or method
handleWidgetDuplicate: function(widgetElement) {
    console.log('🔄 Widget duplicate clicked');
    
    // Extract widget data
    const widgetData = {
        widgetId: widgetElement.dataset.previewWidget,
        sectionId: widgetElement.dataset.sectionId,
        widgetName: widgetElement.dataset.widgetName
    };
    
    // Send message to parent using central sender
    if (window.pageBuilderMessageSender) {
        window.pageBuilderMessageSender.widget.duplicateRequested(widgetData);
    }
}
```

### Step 3: Create Action Handler (parent side)

**File**: `actions/pagebuilder-widget-actions.js`

```javascript
class PageBuilderWidgetActions {
    // ... existing methods ...
    
    // NEW ACTION HANDLER
    handleWidgetDuplicate(data, event) {
        console.log('🔄 Handling widget duplicate request:', data);
        
        try {
            const { widgetId, sectionId, widgetName } = data;
            
            if (!widgetId) {
                console.warn('⚠️ Widget ID not provided for duplicate request');
                return;
            }
            
            // Confirm with user
            const confirmDuplicate = confirm(
                `Do you want to duplicate the widget "${widgetName || 'Unknown Widget'}"?`
            );
            
            if (confirmDuplicate) {
                // TODO: Implement actual widget duplication
                this.duplicateWidget(widgetId, sectionId);
            }
            
        } catch (error) {
            console.error('❌ Error handling widget duplicate:', error);
            this.showError('Failed to duplicate widget');
        }
    }
    
    duplicateWidget(widgetId, sectionId) {
        // Implementation for widget duplication
        console.log(`🔄 Duplicating widget ${widgetId} in section ${sectionId}`);
        
        // Make API call to duplicate widget
        // Update UI after successful duplication
        
        this.showSuccess('Widget duplicated successfully');
    }
}
```

### Step 4: Register the Handler (parent side)

**File**: `page-builder.js` - in the initialization section

```javascript
// In the DOMContentLoaded event listener
document.addEventListener('DOMContentLoaded', function() {
    // ... existing code ...
    
    if (window.PageBuilderWidgetActions) {
        const widgetActions = new PageBuilderWidgetActions();
        
        // Register existing handlers...
        
        // REGISTER NEW HANDLER
        messageRouter.registerHandler('widget-duplicate-requested', (data, event) => {
            widgetActions.handleWidgetDuplicate(data, event);
        });
        
        window.pageBuilderWidgetActions = widgetActions;
    }
});
```

### Step 5: Include Action Handler Script

**File**: `show.blade.php`

```html
@push('scripts')
<!-- Page Builder Action Handlers -->
<script src="{{ asset('assets/admin/js/page-builder/actions/pagebuilder-widget-actions.js') }}?v={{ time() }}"></script>
<!-- ... other scripts ... -->
@endpush
```

## Complete Example: Adding Widget Copy Action

### 1. Iframe Side - Message Sender Usage

```javascript
// In pagebuilder-widget-preview.js
handleWidgetCopy: function(widgetElement) {
    const widgetData = {
        widgetId: widgetElement.dataset.previewWidget,
        widgetName: widgetElement.dataset.widgetName,
        sectionId: widgetElement.dataset.sectionId
    };
    
    // Use central message sender
    window.pageBuilderMessageSender.widget.copyRequested(widgetData);
}
```

### 2. Central Message Sender Extension

```javascript
// In pagebuilder-preview-helper.js
widget: {
    // ... existing methods ...
    copyRequested: function(widgetData) {
        window.pageBuilderMessageSender.sendToParent('widget-copy-requested', widgetData);
    }
}
```

### 3. Parent Side - Action Handler

```javascript
// In actions/pagebuilder-widget-actions.js
handleWidgetCopy(data, event) {
    const { widgetId, widgetName } = data;
    
    // Copy widget to clipboard (conceptually)
    this.copyWidgetToClipboard(widgetId);
    this.showSuccess(`Widget "${widgetName}" copied to clipboard`);
}
```

### 4. Handler Registration

```javascript
// In page-builder.js
messageRouter.registerHandler('widget-copy-requested', (data, event) => {
    widgetActions.handleWidgetCopy(data, event);
});
```

## Benefits of This Architecture

### ✅ Scalability
- Easy to add new actions without modifying existing code
- Each domain (page/section/widget) has its own action handler file
- Message types are clearly organized and documented

### ✅ Maintainability
- Single responsibility principle - each file handles one domain
- Clear separation between iframe logic and parent logic
- Standardized message format across all actions

### ✅ Debugging
- Complete message history stored in router
- Console logging at each step of the flow
- Clear error handling and user feedback

### ✅ Testability
- Each action handler can be unit tested independently
- Message sender can be mocked for testing
- Clear input/output contracts for each method

## Debugging Tools

### Message History
```javascript
// Get all message history
window.pageBuilderMessageRouter.getMessageHistory();

// Clear message history
window.pageBuilderMessageRouter.clearMessageHistory();
```

### Console Logging
All components log their actions with consistent prefixes:
- 📤 Message sent from iframe
- 📥 Message received by parent
- ✅ Action handled successfully
- ❌ Error in action handling
- ⚠️ Warnings and missing data

## Best Practices

### 1. Always Use Central Message Sender
```javascript
// ✅ GOOD
window.pageBuilderMessageSender.page.editRequested(data);

// ❌ BAD - Direct postMessage
window.parent.postMessage({...}, '*');
```

### 2. Handle Errors Gracefully
```javascript
try {
    // Action logic here
    this.showSuccess('Action completed successfully');
} catch (error) {
    console.error('❌ Error in action:', error);
    this.showError('Action failed. Please try again.');
}
```

### 3. Always Validate Input Data
```javascript
handleSomeAction(data, event) {
    const { requiredField } = data;
    
    if (!requiredField) {
        console.warn('⚠️ Required field missing');
        return;
    }
    
    // Proceed with action...
}
```

### 4. Provide User Feedback
```javascript
// Always inform the user about action results
this.showSuccess('Section added successfully');
this.showError('Failed to add section');
this.showInfo('This feature will be available soon');
```

## Future Extensions

This architecture supports future enhancements:
- Message priorities and queuing
- Response messages from parent to iframe  
- Action undo/redo system
- Real-time collaborative editing
- Plugin system for custom actions

---

*Generated: Page Builder Message Architecture v1.0*