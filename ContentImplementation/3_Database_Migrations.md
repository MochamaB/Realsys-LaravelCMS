# Content-Driven CMS Architecture: Database Migrations

This document details all the database migrations required to implement the content-driven CMS architecture. Each migration is presented with its Laravel migration code.

## Phase 1: Content Management System Migrations

### 1. Create Content Types Table

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContentTypesTable extends Migration
{
    /**
     * Run the migration.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('content_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('key')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('content_types');
    }
}
```

### 2. Create Content Type Fields Table

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContentTypeFieldsTable extends Migration
{
    /**
     * Run the migration.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('content_type_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('content_type_id');
            $table->string('name');
            $table->string('key');
            $table->string('type'); // text, textarea, rich_text, image, file, date, etc.
            $table->boolean('required')->default(false);
            $table->text('description')->nullable();
            $table->text('validation_rules')->nullable();
            $table->text('default_value')->nullable();
            $table->integer('order_index')->default(0);
            $table->timestamps();
            
            $table->unique(['content_type_id', 'key']);
            $table->foreign('content_type_id')
                  ->references('id')
                  ->on('content_types')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('content_type_fields');
    }
}
```

### 3. Create Content Type Field Options Table

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContentTypeFieldOptionsTable extends Migration
{
    /**
     * Run the migration.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('content_type_field_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('field_id');
            $table->string('label');
            $table->string('value');
            $table->integer('order_index')->default(0);
            $table->timestamps();
            
            $table->foreign('field_id')
                  ->references('id')
                  ->on('content_type_fields')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('content_type_field_options');
    }
}
```

### 4. Create Content Items Table

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContentItemsTable extends Migration
{
    /**
     * Run the migration.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('content_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('content_type_id');
            $table->string('title');
            $table->string('slug')->unique();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('content_type_id')
                  ->references('id')
                  ->on('content_types')
                  ->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('content_items');
    }
}
```

### 5. Create Content Field Values Table

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContentFieldValuesTable extends Migration
{
    /**
     * Run the migration.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('content_field_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('content_item_id');
            $table->unsignedBigInteger('field_id');
            $table->text('value')->nullable();
            $table->timestamps();
            
            $table->unique(['content_item_id', 'field_id']);
            $table->foreign('content_item_id')
                  ->references('id')
                  ->on('content_items')
                  ->onDelete('cascade');
            $table->foreign('field_id')
                  ->references('id')
                  ->on('content_type_fields')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('content_field_values');
    }
}
```

## Phase 2: Widget System Migrations

### 6. Create Widget Content Queries Table

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWidgetContentQueriesTable extends Migration
{
    /**
     * Run the migration.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('widget_content_queries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('content_type_id')->nullable();
            $table->integer('limit')->nullable();
            $table->integer('offset')->default(0);
            $table->string('order_by')->nullable();
            $table->enum('order_direction', ['asc', 'desc'])->default('desc');
            $table->timestamps();
            
            $table->foreign('content_type_id')
                  ->references('id')
                  ->on('content_types')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('widget_content_queries');
    }
}
```

### 7. Create Widget Content Query Filters Table

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWidgetContentQueryFiltersTable extends Migration
{
    /**
     * Run the migration.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('widget_content_query_filters', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('query_id');
            $table->unsignedBigInteger('field_id')->nullable();
            $table->string('field_key')->nullable();
            $table->string('operator'); // equals, not_equals, contains, greater_than, etc.
            $table->text('value')->nullable();
            $table->string('condition_group')->nullable(); // For AND/OR grouping
            $table->timestamps();
            
            $table->foreign('query_id')
                  ->references('id')
                  ->on('widget_content_queries')
                  ->onDelete('cascade');
            $table->foreign('field_id')
                  ->references('id')
                  ->on('content_type_fields')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('widget_content_query_filters');
    }
}
```

### 8. Create Widget Display Settings Table

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWidgetDisplaySettingsTable extends Migration
{
    /**
     * Run the migration.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('widget_display_settings', function (Blueprint $table) {
            $table->id();
            $table->string('layout')->nullable();
            $table->string('view_mode')->nullable();
            $table->string('pagination_type')->nullable();
            $table->integer('items_per_page')->nullable();
            $table->string('empty_text')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('widget_display_settings');
    }
}
```

### 9. Update Widgets Table

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateWidgetsTableForContentSystem extends Migration
{
    /**
     * Run the migration.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('widgets', function (Blueprint $table) {
            $table->unsignedBigInteger('content_query_id')->nullable()->after('widget_type_id');
            $table->unsignedBigInteger('display_settings_id')->nullable()->after('content_query_id');
            
            $table->foreign('content_query_id')
                  ->references('id')
                  ->on('widget_content_queries')
                  ->onDelete('set null');
            
            $table->foreign('display_settings_id')
                  ->references('id')
                  ->on('widget_display_settings')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('widgets', function (Blueprint $table) {
            $table->dropForeign(['content_query_id']);
            $table->dropForeign(['display_settings_id']);
            $table->dropColumn('content_query_id');
            $table->dropColumn('display_settings_id');
        });
    }
}
```

## Phase 3: Content Relationships Migrations

### 10. Create Content Relationships Table

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContentRelationshipsTable extends Migration
{
    /**
     * Run the migration.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('content_relationships', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_item_id');
            $table->unsignedBigInteger('target_item_id');
            $table->string('relationship_type');
            $table->integer('weight')->default(0);
            $table->timestamps();
            
            $table->foreign('source_item_id')
                  ->references('id')
                  ->on('content_items')
                  ->onDelete('cascade');
            
            $table->foreign('target_item_id')
                  ->references('id')
                  ->on('content_items')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('content_relationships');
    }
}
```

### 11. Create Content Categories Table

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContentCategoriesTable extends Migration
{
    /**
     * Run the migration.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('content_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('content_type_id')->nullable();
            $table->timestamps();
            
            $table->foreign('parent_id')
                  ->references('id')
                  ->on('content_categories')
                  ->onDelete('set null');
            
            $table->foreign('content_type_id')
                  ->references('id')
                  ->on('content_types')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('content_categories');
    }
}
```

### 12. Create Content Item Categories Pivot Table

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContentItemCategoriesTable extends Migration
{
    /**
     * Run the migration.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('content_item_categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('content_item_id');
            $table->unsignedBigInteger('category_id');
            $table->timestamps();
            
            $table->unique(['content_item_id', 'category_id']);
            
            $table->foreign('content_item_id')
                  ->references('id')
                  ->on('content_items')
                  ->onDelete('cascade');
            
            $table->foreign('category_id')
                  ->references('id')
                  ->on('content_categories')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('content_item_categories');
    }
}
```

## Laravel Migration Commands

To create these migrations, run the following commands:

```bash
php artisan make:migration CreateContentTypesTable
php artisan make:migration CreateContentTypeFieldsTable
php artisan make:migration CreateContentTypeFieldOptionsTable
php artisan make:migration CreateContentItemsTable
php artisan make:migration CreateContentFieldValuesTable
php artisan make:migration CreateWidgetContentQueriesTable
php artisan make:migration CreateWidgetContentQueryFiltersTable
php artisan make:migration CreateWidgetDisplaySettingsTable
php artisan make:migration UpdateWidgetsTableForContentSystem
php artisan make:migration CreateContentRelationshipsTable
php artisan make:migration CreateContentCategoriesTable
php artisan make:migration CreateContentItemCategoriesTable
```

To run all migrations:

```bash
php artisan migrate
```

To roll back migrations:

```bash
php artisan migrate:rollback
```
