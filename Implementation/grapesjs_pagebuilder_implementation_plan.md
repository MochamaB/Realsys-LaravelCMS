# GrapesJS Page Builder Implementation Plan (Section-Based)

## Overview
This plan details how to integrate GrapesJS as a visual page builder for the CMS, fully aligned with the section-based architecture (Page, PageSection, PageSectionWidget, Widget). The builder will:
- Be section-aware (sections = drop zones for widgets)
- Allow drag-and-drop and configuration of widgets within sections
- Use the backend TemplateRenderer for true WYSIWYG live preview
- Persist all structure and settings in the database (not as flat HTML/CSS)

---

## 1. Requirements & Goals
- Visual drag-and-drop builder for admin users
- Sidebar with:
  - Blocks/Widgets panel (custom widgets)
  - Style Manager
  - Layer Manager
  - **Widget/Content Manager**: Select or create content items for widgets
- Each page is composed of PageSections (from the template)
- Each PageSection contains PageSectionWidgets (ordered, with settings)
- All changes are persisted in the database using the existing models
- Live preview uses the actual frontend rendering logic (TemplateRenderer)

---

## 2. Data Model Recap
- **Page**: Has many PageSections (ordered by position)
- **PageSection**: Belongs to Page and TemplateSection; has many PageSectionWidgets
- **PageSectionWidget**: Belongs to PageSection and Widget; stores settings, position, content query, etc.
- **Widget**: Defines reusable blocks/components
- **TemplateRenderer**: Renders the page using the above structure

---

## 3. Backend Changes
### 3.1. API Endpoints
- **GET /api/pages/{id}/sections**: List all PageSections for a page (with widgets)
- **POST /api/pages/{id}/sections**: Add/reorder/remove PageSections
- **GET /api/sections/{id}/widgets**: List all widgets in a section
- **POST /api/sections/{id}/widgets**: Add/reorder/remove widgets in a section
- **PUT /api/widgets/{id}**: Update widget settings/content association
- **GET /api/widgets**: List available widgets (for block panel)
- **GET /api/content/{type}**: List content items by type (for content picker)
- **POST /api/content/{type}**: Create new content item
- **POST /api/pages/{id}/preview**: Render a live preview using TemplateRenderer (returns HTML)

### 3.2. Data Persistence
- All structure is persisted in PageSections and PageSectionWidgets tables
- Widget settings, content associations, and layout are stored in PageSectionWidget
- No need to save flat HTML/CSS for the builder

---

## 4. Frontend (GrapesJS) Integration
### 4.1. Section-Aware Canvas
- Render the page as a series of sections (from the template/PageSections)
- Each section is a drop zone for widgets (PageSectionWidgets)
- Allow reordering of sections and widgets within sections

### 4.2. Widget Drag-and-Drop
- Populate the block panel with available widgets
- Allow dragging widgets into sections and reordering them
- When a widget is added, create a new PageSectionWidget via API

### 4.3. Widget/Content Manager Panel
- When a widget is selected, show a panel for:
  - Widget settings (fields, queries, styles)
  - Content picker/creator (if applicable)
- Save changes to the PageSectionWidget via API

### 4.4. Live Preview
- On any change, send the current structure to the backend preview endpoint
- The backend uses TemplateRenderer to return the rendered HTML
- Display the HTML in the builder for true WYSIWYG

### 4.5. Saving
- On save, persist the current structure (sections, widgets, settings) via API
- No need to save HTML/CSS; always render from the database structure

---

## 5. User Experience (UX) Flow
1. Admin creates a new page (title, slug, template, etc.)
2. Admin is redirected to the designer view (GrapesJS canvas)
3. The builder loads all PageSections for the page (from the template)
4. Admin drags widgets into sections, reorders, and configures them
5. For each widget, admin selects/creates content items and sets options
6. Admin sees a live preview rendered by the backend
7. Admin saves; structure is persisted in the database
8. On the frontend, the page is rendered using TemplateRenderer and the saved structure

---

## 6. Security & Permissions
- Only authorized users can access builder and content APIs
- All data validated on the backend before saving

---

## 7. Optional Enhancements
- Versioning for page builder data
- Preview mode for pages before publishing
- Reusable widget/content templates
- Drag-and-drop reordering of sections and widgets
- Multi-language support for content items

---

## 8. Milestones & Tasks
1. **Backend API for section/widget CRUD and preview**
2. **GrapesJS integration in designer view (section-aware)**
3. **Custom blocks/widgets registration**
4. **Widget/Content Manager panel implementation**
5. **Live preview integration**
6. **Saving/loading logic**
7. **Frontend rendering logic**
8. **Testing & QA**
9. **Documentation & training**

---

## 9. References
- [GrapesJS Documentation](https://grapesjs.com/docs/)
- [GrapesJS Custom Blocks](https://grapesjs.com/docs/modules/BlockManager.html)
- [GrapesJS Plugins](https://grapesjs.com/plugins.html)
- [dotCMS Page Builder Inspiration](https://dotcms.com)

---

**End of Plan** 