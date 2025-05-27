<?php

/**
 * Admin Assets Downloader
 * 
 * This script downloads the missing admin template assets
 * and places them in the correct directories.
 */

// Define base directories
$baseDir = __DIR__ . '/public/assets/admin/libs';
$tempDir = __DIR__ . '/temp_downloads';

// Create temp directory if it doesn't exist
if (!is_dir($tempDir)) {
    mkdir($tempDir, 0755, true);
}

// Create libraries directory if it doesn't exist
if (!is_dir($baseDir)) {
    mkdir($baseDir, 0755, true);
}

// List of assets to download
$assets = [
    'jquery' => [
        'name' => 'jQuery',
        'url' => 'https://code.jquery.com/jquery-3.6.0.min.js',
        'path' => '/jquery/jquery.min.js'
    ],
    'metismenu' => [
        'name' => 'MetisMenu',
        'url' => 'https://cdnjs.cloudflare.com/ajax/libs/metisMenu/3.0.7/metisMenu.min.js',
        'path' => '/metismenu/metisMenu.min.js'
    ],
    'jsvectormap-js' => [
        'name' => 'jsVectorMap JS',
        'url' => 'https://cdn.jsdelivr.net/npm/jsvectormap@1.5.1/dist/js/jsvectormap.min.js',
        'path' => '/jsvectormap/js/jsvectormap.min.js'
    ],
    'jsvectormap-css' => [
        'name' => 'jsVectorMap CSS',
        'url' => 'https://cdn.jsdelivr.net/npm/jsvectormap@1.5.1/dist/css/jsvectormap.min.css',
        'path' => '/jsvectormap/css/jsvectormap.min.css'
    ],
    'world-map' => [
        'name' => 'World Map',
        'url' => 'https://cdn.jsdelivr.net/npm/jsvectormap@1.5.1/dist/maps/world-merc.js',
        'path' => '/jsvectormap/maps/world-merc.js'
    ]
];

// Function to download a file
function downloadFile($url, $destination) {
    if (!file_exists(dirname($destination))) {
        mkdir(dirname($destination), 0755, true);
    }
    
    $content = file_get_contents($url);
    if ($content === false) {
        return false;
    }
    
    return file_put_contents($destination, $content);
}

// Download all assets
echo "Starting download of missing admin assets...\n";

foreach ($assets as $key => $asset) {
    $destPath = $baseDir . $asset['path'];
    echo "Downloading {$asset['name']}... ";
    
    if (file_exists($destPath)) {
        echo "Already exists. Skipping.\n";
        continue;
    }
    
    if (downloadFile($asset['url'], $destPath)) {
        echo "Done.\n";
    } else {
        echo "Failed to download.\n";
    }
}

echo "\nAdmin assets download complete!\n";
echo "Please check the console for any errors.\n";

// Clean up
if (is_dir($tempDir)) {
    // Simple cleanup - rmdir only removes empty directories
    // For a more complex cleanup you'd need a recursive function
    rmdir($tempDir);
}

echo "\nTo run this script:\n";
echo "1. Navigate to your project directory in the terminal\n";
echo "2. Run: php download-admin-assets.php\n";
echo "\nAfter running this script, refresh your admin page to see if the errors are resolved.\n";
