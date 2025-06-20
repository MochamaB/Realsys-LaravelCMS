# Advanced Settings Module Implementation Plan

This document outlines the hierarchical implementation plan for building the advanced settings module.

---

### **Phase 1: Foundation - Database & Core Components**

This phase lays the essential groundwork. All subsequent work will be built on top of these core elements.

1.  **Database Schema Construction**
    *   **Action:** Create three new database migration files.
    *   **Details:**
        *   `create_setting_groups_table`: Will contain `id`, `name`, `description`, `display_order`, and a nullable `parent_group_id`.
        *   `create_setting_definitions_table`: Will contain `id`, `key`, `name`, `description`, `data_type`, `default_value`, `is_user_configurable`, `module_name`, `group_id` (foreign key to groups), `validation_rules`, and `display_order`.
        *   `create_setting_values_table`: Will contain `id`, `setting_definition_id` (foreign key), `value`, `scope` (string), `scope_id` (nullable integer), and `created_by`.
    *   **Goal:** Establish the database structure that defines the relationships between groups, definitions, and their stored values.

2.  **Eloquent Model Creation**
    *   **Action:** Create three Eloquent models: `SettingGroup`, `SettingDefinition`, `SettingValue`.
    *   **Details:**
        *   Define the `hasMany` and `belongsTo` relationships between the models (e.g., a `SettingGroup` has many `SettingDefinitions`).
        *   In `SettingValue`, implement an accessor (`getTypedValueAttribute`) to automatically cast the stored `value` to the `data_type` specified in its parent `SettingDefinition`.
        *   In `SettingValue`, create a mutator/accessor pair to handle automatic encryption/decryption for any `value` whose definition's `data_type` is `secret` or `password`.
    *   **Goal:** Create an object-oriented way to interact with the database tables, encapsulating business logic like type casting and encryption.

3.  **Initial Data Seeding**
    *   **Action:** Create seeder classes for the new tables.
    *   **Details:**
        *   `SettingGroupSeeder`: Pre-populate essential groups like "General", "Mail", "Admin Theme", and "Social Media".
        *   `SettingDefinitionSeeder`: Define the initial, core application settings (e.g., `general.site_name`, `mail.driver`, `mail.host`, `admin_theme.logo`). Assign them to their respective groups.
        *   `DatabaseSeeder`: Update this file to call the new seeders in the correct order.
    *   **Goal:** Ensure that a new application installation has a default, functional set of settings from the very beginning.

---

### **Phase 2: Backend Service Architecture**

This phase builds the "brains" of the operation, abstracting all the complex logic into a single, reusable service.

1.  **Develop the `SettingsService`**
    *   **Action:** Create a new class at `app/Services/SettingsService.php`.
    *   **Details:**
        *   Implement a `get(string $key, $scopeId = null)` method. This will contain the core retrieval logic: check for a user-scoped value first, then a global-scoped value, and finally fall back to the default from the definition table.
        *   Implement a `set(string $key, $value, string $scope = 'global', $scopeId = null)` method to handle the creation or updating of a setting in the `SettingValues` table.
        *   Integrate Laravel's caching mechanism. The `get` method must check the cache before hitting the database, and the `set` method must clear the relevant cache entry upon a successful write.
    *   **Goal:** Centralize all settings logic into one testable, maintainable class.

2.  **Application Integration**
    *   **Action:** Create a `SettingsServiceProvider` and a `Settings` Facade.
    *   **Details:**
        *   Register the `SettingsService` as a singleton in the service provider.
        *   Create the Facade to provide simple, static-like access (e.g., `Settings::get('...')`).
        *   Register the provider and facade alias in `config/app.php`.
        *   Create a global helper function `setting($key, $default = null)` as a user-friendly shortcut to the service.
    *   **Goal:** Make the `SettingsService` easily accessible from any part of the application (controllers, views, other services).

3.  **Implement Runtime Configuration**
    *   **Action:** Add logic to the `boot()` method of the `SettingsServiceProvider`.
    *   **Details:**
        *   Use the `SettingsService` to retrieve all settings from the "Mail" group.
        *   Use Laravel's `config()->set()` function to programmatically override the default mail configuration (`mail.host`, `mail.port`, etc.) with the values from the database.
    *   **Goal:** Make the application's mailer (and potentially other core systems) use the settings from the admin panel instead of the `.env` file values.

---

### **Phase 3: Admin Panel User Interface**

This phase focuses on creating the user-facing interface for administrators to manage the settings.

1.  **Controller & Routing**
    *   **Action:** Create a new `SettingsController` in the `Admin` namespace and define its route in `routes/admin.php`.
    *   **Details:**
        *   The `index()` method will fetch all `SettingGroups` with their related `SettingDefinitions` and pass them to a view.
        *   The `update()` method will handle the form submission.
    *   **Goal:** Establish the endpoint and control logic for the settings page.

2.  **Dynamic Settings View**
    *   **Action:** Create a Blade view file at `resources/views/admin/settings/index.blade.php`.
    *   **Details:**
        *   Render a tabbed or accordion interface based on the `SettingGroup` data. Support nested groups using the `parent_group_id`.
        *   Within each group, loop through the `SettingDefinition`s.
        *   Use Blade components or `@if` statements to render the correct HTML input (`<input type="text">`, `<input type="file">`, `<select>`, `<textarea>`) based on each definition's `data_type`.
        *   Populate the inputs with the existing global values using the `setting()` helper function.
    *   **Goal:** Create a user-friendly form that is generated dynamically from the database schema.

3.  **Update and Validation Logic**
    *   **Action:** Implement the `update()` method in `SettingsController`.
    *   **Details:**
        *   Loop through the submitted form data.
        *   For each setting, use the `SettingsService` (`Settings::set(...)`) to save the new value with a `global` scope.
        *   Before saving, validate the incoming data using the `validation_rules` from the corresponding `SettingDefinition`.
        *   Handle file uploads separately by storing the file and saving its path as the setting's value.
    *   **Goal:** Securely save, validate, and cache-bust the settings when an administrator makes changes.

---

### **Phase 4: User-Scoped Settings & Finalization**

This phase extends the system to handle individual user preferences.

1.  **User Profile Integration**
    *   **Action:** Modify the existing user profile page and its controller.
    *   **Details:**
        *   On the profile edit page, display form inputs for settings where `is_user_configurable` is `true`.
        *   In the profile update method, save these values using the `SettingsService`, but this time specifying the `'user'` scope and the user's ID: `Settings::set('key', 'value', 'user', $user->id)`.
    *   **Goal:** Allow users to personalize their experience, with their choices overriding the global defaults.

2.  **Testing and Refinement**
    *   **Action:** Write feature and unit tests.
    *   **Details:**
        *   Test the `SettingsService` retrieval hierarchy.
        *   Test the admin UI for saving and validating global settings.
        *   Test that a user setting correctly overrides a global setting.
        *   Test special data types like file uploads and encrypted values.
    *   **Goal:** Ensure the entire system is robust, secure, and functions as expected.
