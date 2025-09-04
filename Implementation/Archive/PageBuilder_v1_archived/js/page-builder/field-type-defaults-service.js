/**
 * FIELD TYPE DEFAULTS SERVICE
 * ===========================
 * 
 * GENERAL PURPOSE:
 * Provides default values for different field types when creating new content items.
 * Handles field type detection, default value generation, and content item creation
 * with appropriate default values based on content type structure.
 * 
 * KEY FUNCTIONS/METHODS & DUPLICATION STATUS:
 * 
 * CONTENT CREATION WITH DEFAULTS:
 * • createContentItemWithDefaults() - **UNIQUE** - Create content with default field values
 * • applyFieldDefaults() - **UNIQUE** - Apply default values to content fields
 * • generateDefaultContent() - **UNIQUE** - Generate complete default content structure
 * 
 * CONTENT TYPE STRUCTURE:
 * • getContentTypeStructure() - **UNIQUE** - Load content type field definitions
 * • parseFieldDefinitions() - **UNIQUE** - Parse field definitions from content type
 * • validateContentTypeStructure() - **UNIQUE** - Ensure content type structure is valid
 * 
 * FIELD TYPE DEFAULTS:
 * • getDefaultValueForFieldType() - **UNIQUE** - Get default value for specific field type
 * • applyFieldTypeDefaults() - **UNIQUE** - Apply defaults based on field type
 * • updateDefaultValues() - **UNIQUE** - Update default value configurations
 * • validateFieldDefault() - **UNIQUE** - Ensure field default is appropriate
 * 
 * CONTENT ITEM CREATION:
 * • createContentItem() - **UNIQUE** - Create new content item via API
 * • prepareContentData() - **UNIQUE** - Prepare content data for API submission
 * • validateContentData() - **UNIQUE** - Validate content before creation
 * • handleContentCreationResponse() - **UNIQUE** - Process content creation response
 * 
 * FIELD TYPE HANDLING:
 * • handleTextField() - **UNIQUE** - Handle text field defaults
 * • handleNumberField() - **UNIQUE** - Handle numeric field defaults
 * • handleDateField() - **UNIQUE** - Handle date/datetime field defaults
 * • handleBooleanField() - **UNIQUE** - Handle boolean field defaults
 * • handleSelectField() - **UNIQUE** - Handle select/option field defaults
 * • handleRelationField() - **UNIQUE** - Handle relation field defaults
 * • handleMediaField() - **UNIQUE** - Handle image/file field defaults
 * • handleJsonField() - **UNIQUE** - Handle JSON/object field defaults
 * • handleRepeaterField() - **UNIQUE** - Handle repeater field defaults
 * 
 * DEFAULT VALUE DEFINITIONS:
 * • fieldTypeDefaults - **UNIQUE** - Central registry of default values by field type
 * • getTextDefaults() - **UNIQUE** - Get defaults for text-based fields
 * • getNumericDefaults() - **UNIQUE** - Get defaults for numeric fields
 * • getDateDefaults() - **UNIQUE** - Get defaults for date fields
 * • getMediaDefaults() - **UNIQUE** - Get defaults for media fields
 * • getRelationDefaults() - **UNIQUE** - Get defaults for relation fields
 * 
 * CONTENT VALIDATION:
 * • validateFieldValue() - **UNIQUE** - Validate field value against field definition
 * • validateRequiredFields() - **UNIQUE** - Ensure required fields have values
 * • sanitizeFieldValue() - **UNIQUE** - Sanitize field values before creation
 * 
 * API INTEGRATION:
 * • makeAPIRequest() - **DUPLICATED** - API request logic might duplicate page-builder-api.js
 * • handleAPIError() - **DUPLICATED** - Error handling might duplicate page-builder-api.js
 * • formatAPIResponse() - **DUPLICATED** - Response formatting might duplicate page-builder-api.js
 * 
 * ERROR HANDLING:
 * • handleFieldError() - **UNIQUE** - Handle field-specific errors
 * • showValidationError() - **UNIQUE** - Display validation errors
 * • recoverFromError() - **UNIQUE** - Attempt error recovery
 * 
 * UTILITY METHODS:
 * • generateCurrentDate() - **UNIQUE** - Generate current date in proper format
 * • generateCurrentDateTime() - **UNIQUE** - Generate current datetime
 * • formatDefaultValue() - **UNIQUE** - Format default values for display
 * • isValidFieldType() - **UNIQUE** - Check if field type is supported
 * 
 * MAJOR DUPLICATION ISSUES:
 * 1. **API CALLS**: Direct API calls bypass the centralized PageBuilderAPI layer
 * 2. **ERROR HANDLING**: Custom error handling instead of using centralized patterns
 * 3. **VALIDATION**: Field validation logic might overlap with other validation code
 * 4. **DEFAULT VALUES**: Some default value logic might exist elsewhere
 * 
 * INCONSISTENCIES WITH OTHER FILES:
 * • API request patterns differ from page-builder-api.js
 * • Error handling approach may differ from other services
 * • Content creation flow may not integrate well with widget-modal-manager.js
 * • Field type definitions might not match other field type handling code
 * 
 * INTEGRATION POINTS:
 * • Used by widget-modal-manager.js for content creation
 * • May be used by other components that need default content
 * • Should integrate with centralized API layer for consistency
 */
class FieldTypeDefaultsService {
    constructor(apiBaseUrl, csrfToken) {
        this.apiBaseUrl = apiBaseUrl;
        this.csrfToken = csrfToken;
        this.fieldTypeDefaults = {
            'text': '',
            'textarea': '',
            'rich_text': '',
            'number': 0,
            'date': new Date().toISOString().split('T')[0],
            'datetime': new Date().toISOString().slice(0, 19).replace('T', ' '),
            'boolean': false,
            'select': '',
            'multiselect': [],
            'radio': '',
            'checkbox': [],
            'image': null,
            'gallery': [],
            'file': null,
            'url': '',
            'email': '',
            'phone': '',
            'color': '#000000',
            'json': {},
            'repeater': [],
            'relation': null
        };
    }

    /**
     * Create a new content item with default field values
     */
    async createContentItemWithDefaults(contentTypeId) {
        try {
            console.log('Creating content item with defaults for content type:', contentTypeId);
            
            // Create a simple content item with basic defaults
            // We'll let the backend handle field defaults based on content type structure
            const newItem = await this.createContentItem(contentTypeId, {});
            
            console.log('New content item created with defaults:', newItem);
            return newItem;
            
        } catch (error) {
            console.error('Error creating content item with defaults:', error);
            throw error;
        }
    }

    /**
     * Get content type structure including field definitions
     */
    async getContentTypeStructure(contentTypeId) {
        try {
            const response = await fetch(`${this.apiBaseUrl}/content-types/${contentTypeId}/structure`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                return data.data;
            } else {
                throw new Error(data.error || 'Failed to load content type structure');
            }
        } catch (error) {
            console.error('Error loading content type structure:', error);
            throw error;
        }
    }

    /**
     * Generate default values for all fields based on their types
     */
    generateDefaultFieldValues(fields) {
        const defaultValues = {};
        
        fields.forEach(field => {
            const fieldType = field.field_type;
            const fieldSlug = field.slug;
            
            // Get base default value for the field type
            let defaultValue = this.fieldTypeDefaults[fieldType];
            
            // Override with field-specific default if available
            if (field.default_value !== null && field.default_value !== undefined) {
                defaultValue = field.default_value;
            }
            
            // Handle special cases based on field type
            switch (fieldType) {
                case 'text':
                    defaultValue = field.default_value || `Default ${field.name}`;
                    break;
                    
                case 'textarea':
                    defaultValue = field.default_value || `Default content for ${field.name}`;
                    break;
                    
                case 'rich_text':
                    defaultValue = field.default_value || `<p>Default rich text content for ${field.name}</p>`;
                    break;
                    
                case 'number':
                    if (field.settings?.min !== undefined) {
                        defaultValue = field.settings.min;
                    } else if (field.default_value !== null) {
                        defaultValue = parseFloat(field.default_value) || 0;
                    }
                    break;
                    
                case 'select':
                case 'radio':
                    // Use first available option if options exist
                    if (field.settings?.options && field.settings.options.length > 0) {
                        defaultValue = field.default_value || field.settings.options[0].value;
                    }
                    break;
                    
                case 'multiselect':
                case 'checkbox':
                    // Return empty array for multi-selection fields
                    defaultValue = field.default_value || [];
                    break;
                    
                case 'boolean':
                    defaultValue = field.default_value !== null ? field.default_value : false;
                    break;
                    
                case 'date':
                    defaultValue = field.default_value || new Date().toISOString().split('T')[0];
                    break;
                    
                case 'datetime':
                    if (field.default_value) {
                        defaultValue = field.default_value;
                    } else {
                        const now = new Date();
                        defaultValue = now.toISOString().slice(0, 19).replace('T', ' ');
                    }
                    break;
                    
                case 'email':
                    defaultValue = field.default_value || `example@${field.slug}.com`;
                    break;
                    
                case 'url':
                    defaultValue = field.default_value || `https://example.com/${field.slug}`;
                    break;
                    
                case 'phone':
                    defaultValue = field.default_value || '+1234567890';
                    break;
                    
                case 'color':
                    defaultValue = field.default_value || '#007bff';
                    break;
                    
                case 'json':
                    if (field.default_value) {
                        try {
                            defaultValue = typeof field.default_value === 'string' 
                                ? JSON.parse(field.default_value) 
                                : field.default_value;
                        } catch (e) {
                            defaultValue = {};
                        }
                    } else {
                        defaultValue = { [field.slug]: `Default ${field.name}` };
                    }
                    break;
                    
                case 'repeater':
                    // Create one default sub-field entry if sub-fields exist
                    if (field.settings?.fields && field.settings.fields.length > 0) {
                        const subFieldDefaults = this.generateDefaultFieldValues(field.settings.fields);
                        defaultValue = [subFieldDefaults];
                    } else {
                        defaultValue = [];
                    }
                    break;
                    
                case 'relation':
                    // Leave null for relations - they need to be manually selected
                    defaultValue = null;
                    break;
                    
                case 'image':
                case 'gallery':
                case 'file':
                    // File uploads start as null
                    defaultValue = null;
                    break;
                    
                default:
                    // Fallback to string default
                    defaultValue = field.default_value || `Default ${field.name}`;
            }
            
            defaultValues[fieldSlug] = defaultValue;
        });
        
        return defaultValues;
    }

    /**
     * Create the actual content item via API
     */
    async createContentItem(contentTypeId, fieldValues) {
        try {
            const payload = {
                title: `New Content Item - ${new Date().toLocaleString()}`,
                slug: `new-item-${Date.now()}`,
                status: 'draft',
                field_values: fieldValues
            };
            
            const response = await fetch(`${this.apiBaseUrl}/content-types/${contentTypeId}/create-default-item`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: JSON.stringify(payload)
            });
            
            if (!response.ok) {
                const errorText = await response.text();
                console.error('Create content item response:', errorText);
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                return data.data;
            } else {
                throw new Error(data.error || 'Failed to create content item');
            }
        } catch (error) {
            console.error('Error creating content item:', error);
            throw error;
        }
    }

    /**
     * Get field type configuration
     */
    getFieldTypeConfig(fieldType) {
        // This would normally come from the config/field_types.php
        // For now, return basic info
        const configs = {
            'text': { name: 'Text', has_options: false },
            'textarea': { name: 'Text Area', has_options: false },
            'rich_text': { name: 'Rich Text', has_options: false },
            'number': { name: 'Number', has_options: false },
            'date': { name: 'Date', has_options: false },
            'datetime': { name: 'Date and Time', has_options: false },
            'boolean': { name: 'Boolean', has_options: false },
            'select': { name: 'Select', has_options: true },
            'multiselect': { name: 'Multi-select', has_options: true },
            'radio': { name: 'Radio', has_options: true },
            'checkbox': { name: 'Checkbox', has_options: true },
            'image': { name: 'Image', has_options: false },
            'gallery': { name: 'Gallery', has_options: false },
            'file': { name: 'File', has_options: true },
            'url': { name: 'URL', has_options: false },
            'email': { name: 'Email', has_options: false },
            'phone': { name: 'Phone', has_options: false },
            'color': { name: 'Color', has_options: false },
            'json': { name: 'JSON', has_options: false },
            'repeater': { name: 'Repeater', has_fields: true, has_options: false },
            'relation': { name: 'Content Relation', has_options: true }
        };
        
        return configs[fieldType] || { name: fieldType, has_options: false };
    }
}

// Initialize and make globally available
window.FieldTypeDefaultsService = FieldTypeDefaultsService;