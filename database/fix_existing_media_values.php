<?php

/**
 * One-time script to fix existing ContentFieldValue records
 * that have media attachments but NULL values
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\ContentItem;
use App\Models\ContentFieldValue;
use App\Models\ContentTypeField;
use Illuminate\Support\Facades\DB;

// Bootstrap Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔧 Fixing existing media values in ContentFieldValue table...\n\n";

// Find all image fields that have NULL values but might have media attachments
$imageFields = ContentTypeField::where('field_type', 'image')->get();

$fixedCount = 0;

foreach ($imageFields as $field) {
    echo "Processing field: {$field->name} (ID: {$field->id})\n";
    
    // Find ContentFieldValues for this field that have NULL values
    $nullValues = ContentFieldValue::where('content_type_field_id', $field->id)
        ->whereNull('value')
        ->get();
    
    foreach ($nullValues as $fieldValue) {
        $contentItem = $fieldValue->contentItem;
        if (!$contentItem) continue;
        
        $collectionName = 'field_' . $field->id;
        
        // Check if this content item has media in the expected collection
        if ($contentItem->hasMedia($collectionName)) {
            $media = $contentItem->getFirstMedia($collectionName);
            if ($media) {
                echo "  ✅ Found media ID {$media->id} for ContentItem {$contentItem->id}, updating ContentFieldValue...\n";
                
                // Update the ContentFieldValue with the media ID
                $fieldValue->update(['value' => $media->id]);
                $fixedCount++;
            }
        }
        // Also check generic 'images' collection as fallback
        elseif ($contentItem->hasMedia('images')) {
            $media = $contentItem->getFirstMedia('images');
            if ($media) {
                echo "  ✅ Found media ID {$media->id} in 'images' collection for ContentItem {$contentItem->id}, updating ContentFieldValue...\n";
                
                // Update the ContentFieldValue with the media ID
                $fieldValue->update(['value' => $media->id]);
                $fixedCount++;
            }
        }
    }
}

echo "\n🎉 Fixed {$fixedCount} ContentFieldValue records!\n";
echo "✨ Media IDs are now properly stored in ContentFieldValue.value for consistent lookup.\n"; 