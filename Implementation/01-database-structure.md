# Database Structure

This document outlines the complete database structure for the CMS, replacing JSON columns with a fully relational approach.

## Core Tables

### Themes

```
themes
- id (primary key)
- name (string)
- slug (string, unique)
- description (text)
- version (string)
- author (string)
- screenshot_path (string)
- is_active (boolean)
- created_at (timestamp)
- updated_at (timestamp)
```

### Templates

```
templates
- id (primary key)
- theme_id (foreign key to themes)
- name (string)
- slug (string)
- file_path (string)
- description (text)
- thumbnail_path (string)
- is_active (boolean)
- created_at (timestamp)
- updated_at (timestamp)
```

### Template Sections

```
template_sections
- id (primary key)
- template_id (foreign key to templates)
- name (string)
- slug (string)
- description (text)
- is_required (boolean)
- max_widgets (integer)
- order_index (integer)
- created_at (timestamp)
- updated_at (timestamp)
```

### Pages

```
pages
- id (primary key)
- title (string)
- slug (string, unique)
- description (text)
- content (text, for simple pages)
- template_id (foreign key to templates)
- parent_id (foreign key to pages, nullable)
- is_active (boolean)
- show_in_menu (boolean)
- menu_order (integer)
- meta_title (string)
- meta_description (string)
- meta_keywords (string)
- created_by (foreign key to users)
- updated_by (foreign key to users)
- created_at (timestamp)
- updated_at (timestamp)
```

### Page Sections

```
page_sections
- id (primary key)
- page_id (foreign key to pages)
- template_section_id (foreign key to template_sections)
- is_active (boolean)
- created_at (timestamp)
- updated_at (timestamp)
```

## Widget System Tables

### Widget Types

```
widget_types
- id (primary key)
- name (string)
- slug (string, unique)
- description (text)
- component_path (string)
- icon (string)
- is_active (boolean)
- created_at (timestamp)
- updated_at (timestamp)
```

### Widget Type Fields

```
widget_type_fields
- id (primary key)
- widget_type_id (foreign key to widget_types)
- name (string)
- label (string)
- field_type (string: text, textarea, image, number, boolean, select, etc.)
- is_required (boolean)
- is_repeatable (boolean)
- validation_rules (string)
- help_text (text)
- default_value (string, nullable)
- order_index (integer)
- created_at (timestamp)
- updated_at (timestamp)
```

### Widget Type Field Options

```
widget_type_field_options
- id (primary key)
- widget_type_field_id (foreign key to widget_type_fields)
- value (string)
- label (string)
- order_index (integer)
- created_at (timestamp)
- updated_at (timestamp)
```

### Widgets

```
widgets
- id (primary key)
- widget_type_id (foreign key to widget_types)
- name (string)
- description (text, nullable)
- is_active (boolean)
- created_by (foreign key to users)
- updated_by (foreign key to users)
- created_at (timestamp)
- updated_at (timestamp)
```

### Widget Field Values

```
widget_field_values
- id (primary key)
- widget_id (foreign key to widgets)
- widget_type_field_id (foreign key to widget_type_fields)
- value (text)
- created_at (timestamp)
- updated_at (timestamp)
```

### Widget Repeater Groups

```
widget_repeater_groups
- id (primary key)
- widget_id (foreign key to widgets)
- widget_type_field_id (foreign key to widget_type_fields)
- order_index (integer)
- created_at (timestamp)
- updated_at (timestamp)
```

### Widget Repeater Values

```
widget_repeater_values
- id (primary key)
- widget_repeater_group_id (foreign key to widget_repeater_groups)
- widget_type_field_id (foreign key to widget_type_fields)
- value (text)
- created_at (timestamp)
- updated_at (timestamp)
```

### Page Widgets

```
page_widgets
- id (primary key)
- page_section_id (foreign key to page_sections)
- widget_id (foreign key to widgets)
- order_index (integer)
- created_at (timestamp)
- updated_at (timestamp)
```

## Menu System Tables

### Menus

```
menus
- id (primary key)
- name (string)
- location (string)
- description (text)
- is_active (boolean)
- created_at (timestamp)
- updated_at (timestamp)
```

### Menu Items

```
menu_items
- id (primary key)
- menu_id (foreign key to menus)
- parent_id (foreign key to menu_items, nullable)
- title (string)
- link_type (enum: page, custom, etc.)
- page_id (foreign key to pages, nullable)
- custom_url (string, nullable)
- target (string: _self, _blank, etc.)
- css_class (string, nullable)
- order_index (integer)
- is_active (boolean)
- created_at (timestamp)
- updated_at (timestamp)
```

## User System Tables

```
users
- id (primary key)
- first_name (string)
- surname (string, nullable)
- last_name (string, nullable)
- email (string, unique)
- email_verified_at (timestamp, nullable)
- password (string)
- remember_token (string, nullable)
- phone_number (string, nullable)
- id_number (string, nullable)
- status (enum: active, inactive, suspended, pending)
- two_factor_secret (text, nullable)
- two_factor_recovery_codes (text, nullable)
- two_factor_confirmed_at (timestamp, nullable)
- provider (string, nullable)
- provider_id (string, nullable)
- provider_token (text, nullable)
- provider_refresh_token (text, nullable)
- password_reset_token (string, nullable)
- password_reset_token_expires_at (timestamp, nullable)
- created_at (timestamp)
- updated_at (timestamp)
- deleted_at (timestamp, nullable)
```

### Admins Table
id (primary key)
name (string)
email (string, unique)
email_verified_at (timestamp, nullable)
password (string)
remember_token (string, nullable)
is_super_admin (boolean)
status (string)
created_at (timestamp)
updated_at (timestamp)
deleted_at (timestamp, nullable)
```
roles
- id (primary key)
- name (string)
- description (text)
- created_at (timestamp)
- updated_at (timestamp)
```

```
user_roles
- id (primary key)
- user_id (foreign key to users)
- role_id (foreign key to roles)
- created_at (timestamp)
- updated_at (timestamp)
```

```
permissions
- id (primary key)
- name (string)
- description (text)
- created_at (timestamp)
- updated_at (timestamp)
```

```
role_permissions
- id (primary key)
- role_id (foreign key to roles)
- permission_id (foreign key to permissions)
- created_at (timestamp)
- updated_at (timestamp)
```

## Media Library Tables

```
media
- id (primary key)
- name (string)
- file_name (string)
- mime_type (string)
- size (integer)
- path (string)
- disk (string)
- alt_text (string, nullable)
- caption (text, nullable)
- uploaded_by (foreign key to users)
- created_at (timestamp)
- updated_at (timestamp)
```

## Database Indexes

For optimal performance, the following indexes should be created:

1. `pages`: indexes on `slug`, `parent_id`, `is_active`
2. `widgets`: indexes on `widget_type_id`, `is_active`
3. `widget_field_values`: indexes on `widget_id`, `widget_type_field_id`
4. `widget_repeater_groups`: indexes on `widget_id`, `widget_type_field_id`
5. `page_widgets`: indexes on `page_section_id`, `widget_id`
6. `menu_items`: indexes on `menu_id`, `parent_id`, `is_active`

## Database Relationships

The database structure is designed with clear relationships:

1. One-to-Many:
   - Theme → Templates
   - Template → Template Sections
   - Page → Page Sections
   - Widget Type → Widgets
   - Widget Type → Widget Type Fields
   - Widget Type Field → Widget Type Field Options
   - Menu → Menu Items

2. Many-to-Many:
   - Pages ↔ Widgets (through page_widgets)
   - Users ↔ Roles (through user_roles)
   - Roles ↔ Permissions (through role_permissions)

3. Self-Referential:
   - Pages → Parent Pages
   - Menu Items → Parent Menu Items

This relational structure completely eliminates the need for JSON columns while maintaining flexibility and performance.
