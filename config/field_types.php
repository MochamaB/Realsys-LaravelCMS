<?php

/**
 * Content Type Field Types Configuration
 * This file defines the available field types for content types
 */

return [
    'text' => [
        'name' => 'Text',
        'icon' => 'text',
        'description' => 'Single line of text',
        'has_options' => false,
    ],
    'textarea' => [
        'name' => 'Text Area',
        'icon' => 'align-left',
        'description' => 'Multiple lines of text',
        'has_options' => false,
    ],
    'rich_text' => [
        'name' => 'Rich Text',
        'icon' => 'edit',
        'description' => 'Text with formatting options',
        'has_options' => false,
    ],
    'number' => [
        'name' => 'Number',
        'icon' => 'hash',
        'description' => 'Numeric value',
        'has_options' => true,
    ],
    'date' => [
        'name' => 'Date',
        'icon' => 'calendar',
        'description' => 'Date selector',
        'has_options' => false,
    ],
    'datetime' => [
        'name' => 'Date and Time',
        'icon' => 'calendar-event',
        'description' => 'Date and time selector',
        'has_options' => false,
    ],
    'boolean' => [
        'name' => 'Boolean',
        'icon' => 'check-square',
        'description' => 'Yes/No value',
        'has_options' => false,
    ],
    'select' => [
        'name' => 'Select',
        'icon' => 'list-plus',
        'description' => 'Single selection from options',
        'has_options' => true,
    ],
    'multiselect' => [
        'name' => 'Multi-select',
        'icon' => 'list-check',
        'description' => 'Multiple selections from options',
        'has_options' => true,
    ],
    'image' => [
        'name' => 'Image',
        'icon' => 'image',
        'description' => 'Image upload',
        'has_options' => false,
    ],
    'gallery' => [
        'name' => 'Gallery',
        'icon' => 'images',
        'description' => 'Multiple image uploads',
        'has_options' => false,
    ],
    'file' => [
        'name' => 'File',
        'icon' => 'file',
        'description' => 'File upload',
        'has_options' => true,
    ],
    'url' => [
        'name' => 'URL',
        'icon' => 'link',
        'description' => 'Web URL input',
        'has_options' => false,
    ],
    'email' => [
        'name' => 'Email',
        'icon' => 'at',
        'description' => 'Email address input',
        'has_options' => false,
    ],
    'phone' => [
        'name' => 'Phone',
        'icon' => 'phone',
        'description' => 'Phone number input',
        'has_options' => false,
    ],
    'color' => [
        'name' => 'Color',
        'icon' => 'palette',
        'description' => 'Color selector',
        'has_options' => false,
    ],
    'json' => [
        'name' => 'JSON',
        'icon' => 'code',
        'description' => 'JSON data structure',
        'has_options' => false,
    ],
    'relation' => [
        'name' => 'Content Relation',
        'icon' => 'link-alt',
        'description' => 'Relation to other content types',
        'has_options' => true,
    ],
];
