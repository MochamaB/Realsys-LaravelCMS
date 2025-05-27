# Content-Driven CMS Architecture: Migration Plan

This document outlines the step-by-step process for migrating from the current widget-based architecture to the new content-driven architecture. The plan is designed to minimize disruption while transitioning the system.

## Migration Process Overview

1. Create new database tables without affecting existing ones
2. Implement new models and controllers alongside existing ones
3. Create data migration scripts for content conversion
4. Update widget system to use content queries
5. Update front-end rendering to use the new architecture
6. Gradually phase out old widget field values

## Phase 1: Initial Setup (1-2 weeks)

### Week 1: Database and Models

1. **Create Content Management Tables**
   - Run migrations for content_types
   - Run migrations for content_type_fields
   - Run migrations for content_type_field_options
   - Run migrations for content_items
   - Run migrations for content_field_values

   ```bash
   php artisan make:migration CreateContentTypesTable
   php artisan make:migration CreateContentTypeFieldsTable
   php artisan make:migration CreateContentTypeFieldOptionsTable
   php artisan make:migration CreateContentItemsTable
   php artisan make:migration CreateContentFieldValuesTable
   ```

2. **Create Content Management Models**
   - Create ContentType model
   - Create ContentTypeField model
   - Create ContentTypeFieldOption model
   - Create ContentItem model
   - Create ContentFieldValue model

3. **Create Initial Content Types**
   - Create a seeder for basic content types (Page, Post, etc.)
   - Run the seeder to establish initial content structure

   ```bash
   php artisan make:seeder ContentTypeSeeder
   php artisan db:seed --class=ContentTypeSeeder
   ```

### Week 2: Admin Interface

1. **Create Content Type Management**
   - Create ContentTypeController
   - Create content type views (index, create, edit, show)
   - Implement content type field management

2. **Create Content Item Management**
   - Create ContentItemController
   - Create content item views (index, create, edit, show)
   - Implement field value management based on type

3. **Update Routes**
   - Add routes for content management
   - Update admin menu to include content management section

## Phase 2: Widget System Updates (2-3 weeks)

### Week 3: Widget Query System

1. **Create Widget Query Tables**
   - Run migrations for widget_content_queries
   - Run migrations for widget_content_query_filters
   - Run migrations for widget_display_settings
   - Update widgets table with new foreign keys

   ```bash
   php artisan make:migration CreateWidgetContentQueriesTable
   php artisan make:migration CreateWidgetContentQueryFiltersTable
   php artisan make:migration CreateWidgetDisplaySettingsTable
   php artisan make:migration UpdateWidgetsTableForContentSystem
   ```

2. **Create Widget Query Models**
   - Create WidgetContentQuery model
   - Create WidgetContentQueryFilter model
   - Create WidgetDisplaySetting model
   - Update Widget model with new relationships

3. **Create Widget Content Rendering Service**
   - Implement ContentRenderingService
   - Create field type renderers
   - Create widget rendering methods

### Week 4-5: Widget Admin Interface

1. **Update Widget Controller**
   - Modify WidgetController to support content queries
   - Implement content type selection for widgets
   - Create widget query builder interface

2. **Update Widget Views**
   - Update widget create/edit views
   - Add content query builder interface
   - Add display settings configuration
   - Implement widget preview with content rendering

## Phase 3: Data Migration (1-2 weeks)

### Week 6: Data Migration Scripts

1. **Analyze Existing Widget Data**
   - Identify widget types and their field structures
   - Map widget field values to content types and fields
   - Create a migration plan for each widget type

2. **Create Content Type Import Scripts**
   - Create scripts to generate content types from widget types
   - Implement field mapping from widget fields to content fields
   - Validate content type structures

3. **Create Content Item Import Scripts**
   - Create scripts to convert widget field values to content items
   - Handle media items and file attachments
   - Maintain relationships between content items

### Week 7: Data Migration Execution

1. **Execute Content Type Migration**
   - Run script to create content types from widget types
   - Validate content type creation
   - Make adjustments as needed

2. **Execute Content Item Migration**
   - Run script to create content items from widget field values
   - Validate content item creation
   - Fix any issues with field values or media

3. **Create Widget Content Queries**
   - Generate content queries for existing widgets
   - Link widgets to appropriate content types
   - Configure display settings for each widget

## Phase 4: Rendering System Updates (2-3 weeks)

### Week 8-9: Front-End Rendering

1. **Update Page Controller**
   - Modify PageController to use ContentRenderingService
   - Implement dual rendering paths during transition
   - Add content preview functionality

2. **Create Widget Templates**
   - Create default templates for each content type
   - Implement different view modes (full, teaser, etc.)
   - Create layouts for various display options

3. **Update Theme Templates**
   - Modify theme templates to support content-driven widgets
   - Update widget inclusion to use content rendering
   - Implement caching for rendered content

### Week 10: Testing and Refinement

1. **Comprehensive Testing**
   - Test content management functionality
   - Test widget rendering with content queries
   - Validate front-end display matches expected output
   - Test performance and optimize as needed

2. **Documentation**
   - Update admin documentation for content management
   - Create developer documentation for the new architecture
   - Document APIs and integration points

## Phase 5: Cleanup and Optimization (1 week)

### Week 11: Legacy Code Removal

1. **Remove Legacy Widget Field Value Management**
   - Once all data is migrated, remove old field value management
   - Update relevant controllers and views
   - Clean up database queries

2. **Optimize Performance**
   - Implement caching for content queries
   - Optimize database indexes
   - Refine content rendering for better performance

3. **Final Review**
   - Conduct comprehensive review of the new architecture
   - Address any remaining issues
   - Finalize documentation and training materials

## Migration Commands

Here are the key commands that will be used during the migration process:

```bash
# Create migrations
php artisan make:migration CreateContentTypesTable
php artisan make:migration CreateContentTypeFieldsTable
php artisan make:migration CreateContentTypeFieldOptionsTable
php artisan make:migration CreateContentItemsTable
php artisan make:migration CreateContentFieldValuesTable
php artisan make:migration CreateWidgetContentQueriesTable
php artisan make:migration CreateWidgetContentQueryFiltersTable
php artisan make:migration CreateWidgetDisplaySettingsTable
php artisan make:migration UpdateWidgetsTableForContentSystem

# Run migrations
php artisan migrate

# Create models
php artisan make:model ContentType -m
php artisan make:model ContentTypeField -m
php artisan make:model ContentTypeFieldOption -m
php artisan make:model ContentItem -m
php artisan make:model ContentFieldValue -m
php artisan make:model WidgetContentQuery -m
php artisan make:model WidgetContentQueryFilter -m
php artisan make:model WidgetDisplaySetting -m

# Create controllers
php artisan make:controller Admin/ContentTypeController --resource
php artisan make:controller Admin/ContentTypeFieldController --resource
php artisan make:controller Admin/ContentItemController --resource
php artisan make:controller Admin/WidgetController --resource

# Create seeders
php artisan make:seeder ContentTypeSeeder
php artisan make:seeder ContentMigrationSeeder

# Create services
php artisan make:provider ContentServiceProvider
```

## Rollback Plan

In case of issues during migration, the following rollback steps can be taken:

1. **Phase-by-Phase Rollback**
   - Each phase can be rolled back independently
   - Database changes are additive and don't affect existing functionality until explicitly switched

2. **Database Rollback**
   - Use Laravel's migration rollback to undo schema changes
   ```bash
   php artisan migrate:rollback --step=1
   ```

3. **Code Rollback**
   - Maintain Git branches for each phase
   - Revert to previous branch if issues arise

4. **Dual-Mode Operation**
   - System supports both old and new approaches during transition
   - Can switch back to old approach if needed
