# Content-Driven CMS: Theme Implementation Example

This document explains how a theme like "realsysdefault" with Home, About, Sample Post, and Contact Us pages would be implemented in the content-driven CMS architecture. It details the relationships between themes, templates, pages, page sections, widgets, and content models.

## 1. System Architecture Overview

### Data Flow

```
Themes → Templates → Pages → Page Sections → Widgets → Content
```

### Database Relationships

```
themes
  ↓ (has many)
templates
  ↓ (used by many)
pages
  ↓ (has many)
page_sections
  ↓ (has many through pivot)
widgets
  ↓ (has one)
widget_content_queries
  ↓ (filters by)
content_items
  ↓ (belongs to)
content_types
```

## 2. Theme Registration Process

### Theme Table Data

```
| id | name            | key             | directory            | is_active | is_default |
|----|-----------------|-----------------|----------------------|-----------|------------|
| 1  | RealSys Default | realsysdefault  | realsysdefault       | 1         | 1          |
```

### Theme Import Process

1. Theme files are placed in `resources/themes/realsysdefault/`
2. Theme assets are copied to `public/themes/realsysdefault/`
3. Theme metadata is read from `theme.json`
4. Theme entry is created in the `themes` table
5. Templates are detected and registered in the `templates` table

## 3. Template Registration

### Template Table Data

```
| id | theme_id | name         | key          | file_path                    | description         |
|----|----------|--------------|--------------|------------------------------|---------------------|
| 1  | 1        | Home         | home         | templates/home.blade.php     | Homepage template   |
| 2  | 1        | About        | about        | templates/about.blade.php    | About page template |
| 3  | 1        | Blog Post    | blog-post    | templates/post.blade.php     | Blog post template  |
| 4  | 1        | Contact      | contact      | templates/contact.blade.php  | Contact template    |
```

### Template Section Registration

Templates define sections where content can be placed:

```
| id | template_id | name      | key       | description                         |
|----|-------------|-----------|-----------|-------------------------------------|
| 1  | 1           | Header    | header    | Page header with hero image         |
| 2  | 1           | Content   | content   | Main content area                   |
| 3  | 1           | Sidebar   | sidebar   | Right sidebar for additional content|
| 4  | 1           | Footer    | footer    | Page footer                         |
| 5  | 2           | Header    | header    | About page header                   |
| 6  | 2           | Team      | team      | Team members section                |
| 7  | 2           | Services  | services  | Services section                    |
| 8  | 3           | Post Body | post-body | Main blog post content              |
| 9  | 3           | Comments  | comments  | Comments section                    |
| 10 | 4           | Form      | form      | Contact form section                |
```

## 4. Content Type Setup

### Content Types

```
| id | name        | key         | description                               |
|----|-------------|-------------|-------------------------------------------|
| 1  | Basic Page  | basic-page  | Simple content pages like Home and About  |
| 2  | Blog Post   | blog-post   | Blog articles with author and date        |
| 3  | Team Member | team-member | Staff profiles for the About page         |
| 4  | Service     | service     | Service offerings for the About page      |
| 5  | Contact     | contact     | Contact information                       |
```

### Content Type Fields

For the "Basic Page" content type:
```
| id | content_type_id | name           | key            | type      | required |
|----|-----------------|----------------|----------------|-----------|----------|
| 1  | 1               | Title          | title          | text      | 1        |
| 2  | 1               | Body           | body           | rich_text | 1        |
| 3  | 1               | Featured Image | featured_image | image     | 0        |
| 4  | 1               | Meta Desc      | meta_desc      | textarea  | 0        |
```

For the "Blog Post" content type:
```
| id | content_type_id | name           | key            | type      | required |
|----|-----------------|----------------|----------------|-----------|----------|
| 5  | 2               | Title          | title          | text      | 1        |
| 6  | 2               | Body           | body           | rich_text | 1        |
| 7  | 2               | Featured Image | featured_image | image     | 1        |
| 8  | 2               | Excerpt        | excerpt        | textarea  | 0        |
| 9  | 2               | Author         | author         | reference | 1        |
| 10 | 2               | Publication Date | publish_date | date      | 1        |
| 11 | 2               | Category       | category       | select    | 1        |
```

### Content Type Field Options

Field options are used for select, multi-select, radio, and checkbox fields, providing predefined choices:

```
| id | field_id | label       | value       | order_index |
|----|----------|-------------|-------------|-------------|
| 1  | 11       | Technology  | technology  | 0           |
| 2  | 11       | Business    | business    | 1           |
| 3  | 11       | Design      | design      | 2           |
| 4  | 11       | Marketing   | marketing   | 3           |
```

For a "Team Member" content type with a department field:

```
| id | content_type_id | name       | key        | type   | required |
|----|-----------------|------------|------------|--------|----------|
| 15 | 3               | Department | department | select | 1        |
```

With corresponding options:

```
| id | field_id | label        | value       | order_index |
|----|----------|--------------|-------------|-------------|
| 5  | 15       | Development  | development | 0           |
| 6  | 15       | Marketing    | marketing   | 1           |
| 7  | 15       | Sales        | sales       | 2           |
| 8  | 15       | Management   | management  | 3           |
```

These options appear as dropdown choices in the admin interface, ensuring consistency in field values and enabling filtering/grouping of content items.

Similar fields would be defined for "Service", and "Contact" content types.

## 5. Content Items

### Content Items for "Basic Page"

```
| id | content_type_id | title      | slug       | status    |
|----|-----------------|------------|------------|-----------|
| 1  | 1               | Home       | home       | published |
| 2  | 1               | About Us   | about      | published |
| 3  | 1               | Contact    | contact    | published |
```

### Content Field Values

For the "Home" content item:
```
| id | content_item_id | field_id | value                                     |
|----|-----------------|----------|-------------------------------------------|
| 1  | 1               | 1        | Welcome to RealSys                        |
| 2  | 1               | 2        | <p>This is the homepage content...</p>    |
| 3  | 1               | 3        | [Reference to media library item]         |
```

## 6. Page Setup

### Pages

```
| id | title      | slug       | template_id | status    |
|----|------------|------------|-------------|-----------|
| 1  | Home       | home       | 1           | published |
| 2  | About Us   | about      | 2           | published |
| 3  | Sample Post| sample-post| 3           | published |
| 4  | Contact    | contact    | 4           | published |
```

### Page Sections

```
| id | page_id | template_section_id | order_index |
|----|---------|---------------------|-------------|
| 1  | 1       | 1                   | 0           |
| 2  | 1       | 2                   | 1           |
| 3  | 1       | 4                   | 2           |
| 4  | 2       | 5                   | 0           |
| 5  | 2       | 6                   | 1           |
| 6  | 2       | 7                   | 2           |
```

## 7. Widget Setup

### Widget Types

```
| id | name            | key                | description                      |
|----|-----------------|-------------------|----------------------------------|
| 1  | Content Display | content-display   | Displays a single content item   |
| 2  | Content List    | content-list      | Lists multiple content items     |
| 3  | Team Grid       | team-grid         | Grid of team members             |
| 4  | Service List    | service-list      | List of services                 |
| 5  | Contact Form    | contact-form      | Interactive contact form         |
```

### Widgets

```
| id | name              | widget_type_id | content_query_id | display_settings_id |
|----|-------------------|----------------|------------------|---------------------|
| 1  | Home Hero         | 1              | 1                | 1                   |
| 2  | Home Content      | 1              | 2                | 2                   |
| 3  | About Header      | 1              | 3                | 3                   |
| 4  | Team Members      | 3              | 4                | 4                   |
| 5  | Our Services      | 4              | 5                | 5                   |
| 6  | Sample Blog Post  | 1              | 6                | 6                   |
| 7  | Contact Form      | 5              | 7                | 7                   |
```

### Widget Content Queries

For the "Home Content" widget:
```
| id | content_type_id | limit | offset | order_by | order_direction |
|----|-----------------|-------|--------|----------|----------------|
| 2  | 1               | 1     | 0      | id       | asc            |
```

### Widget Content Query Filters

```
| id | query_id | field_id | field_key | operator | value  |
|----|----------|----------|-----------|----------|--------|
| 1  | 2        | null     | slug      | equals   | home   |
```

### Widget Display Settings

```
| id | layout          | view_mode | pagination_type |
|----|-----------------|-----------|----------------|
| 2  | standard-content| full      | none           |
```

## 8. Page-Section-Widget Relationships

### Page Widgets (Pivot Table)

```
| id | page_section_id | widget_id | order_index |
|----|-----------------|-----------|-------------|
| 1  | 1               | 1         | 0           |
| 2  | 2               | 2         | 0           |
| 3  | 4               | 3         | 0           |
| 4  | 5               | 4         | 0           |
| 5  | 6               | 5         | 0           |
```

## 9. Rendering Process

When a user visits `example.com/about`:

1. **Route Resolution**:
   - `PageController@show` resolves the URL to the "About Us" page (ID: 2)

2. **Page Loading**:
   - Page with ID 2 is loaded with its template (ID: 2, "About")
   - Page sections are loaded (IDs: 4, 5, 6)

3. **Section Processing**:
   - For each section, widgets are loaded in order
   - Section 4 (Header) loads widget 3 (About Header)
   - Section 5 (Team) loads widget 4 (Team Members)
   - Section 6 (Services) loads widget 5 (Our Services)

4. **Widget Content Queries**:
   - Each widget executes its content query
   - Widget 3 loads a single "Basic Page" content item (About Us)
   - Widget 4 loads multiple "Team Member" content items
   - Widget 5 loads multiple "Service" content items

5. **Content Rendering**:
   - `ContentRenderingService` processes each content item
   - Field values are formatted based on field type
   - Media items are processed for images

6. **Template Rendering**:
   - The "About" template is loaded
   - Each section is rendered with its widgets
   - Widgets use their display settings and layout
   - Content is passed to widget templates

7. **Final Output**:
   - Complete HTML is returned to the browser

## 10. Contact Form Special Case

The contact form widget is a special case because it's interactive:

1. **Widget Setup**:
   - Content type "Contact" stores contact configuration
   - Widget type "Contact Form" has a template with the form
   - Widget "Contact Form" is created with display settings

2. **Form Rendering**:
   - Widget template includes the form HTML
   - Form action points to a dedicated controller

3. **Form Submission**:
   - User submits the form
   - `ContactController@submit` processes the submission
   - Validation is performed
   - Email is sent
   - User is redirected with success message

## 11. Theme-Specific Widget Templates

Each theme provides its own widget templates in:
```
resources/themes/realsysdefault/widgets/
```

For example, the contact form widget template would be:
```
resources/themes/realsysdefault/widgets/contact-form.blade.php
```

The template receives standardized data:
```php
<!-- contact-form.blade.php -->
<div class="contact-form {{ $widget->layout }}">
    <h2>{{ $content['title'] }}</h2>
    <form action="{{ route('contact.submit') }}" method="POST">
        @csrf
        <!-- Form fields -->
    </form>
</div>
```

## 12. Theme Switching

If a user switches from "realsysdefault" to another theme:

1. The content remains the same in the database
2. Pages keep their structure and content
3. Only the presentation changes
4. Widget templates from the new theme are used
5. CSS and assets are loaded from the new theme

This separation ensures content persistence across theme changes.
