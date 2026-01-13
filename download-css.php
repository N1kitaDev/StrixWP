<?php
/**
 * Script to download all CSS files from CDN to local directory
 * Run this script once to download all CSS files locally
 */

// Get all style IDs from widget_templates
$styleIds = [4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,44,45,46,47,48,52,53,54,55,56,57,58,59,60,61,62,79,80,81,95,97,98,99,100,101,102,103,104,105,106,107,108,109,110,111,112,113,114,115,116,117,118,119,120,121,122,123,124,125,126,127,128,129,130];

// Get all set IDs from widget_styles (all active and inactive styles)
$setIds = [
    'light-background', 'light-background-large', 'ligth-border', 'ligth-border-3d-large', 
    'ligth-border-large', 'ligth-border-large-red', 'drop-shadow', 'drop-shadow-large', 
    'light-minimal', 'light-minimal-large', 'soft', 'light-clean', 'light-square', 
    'light-background-border', 'blue', 'light-background-large-purple', 'light-background-image', 
    'dark-background'
];

$baseUrl = 'https://cdn.strixmedia.ru/assets/widget-presetted-css/v2/';
$localDir = __DIR__ . '/static/css/widget-presetted-css/v2/';

// Create directory if it doesn't exist
if (!file_exists($localDir)) {
    mkdir($localDir, 0755, true);
}

$downloaded = 0;
$failed = 0;

echo "Starting CSS download...\n\n";

// Download widget CSS files
foreach ($styleIds as $styleId) {
    foreach ($setIds as $setId) {
        $fileName = $styleId . '-' . $setId . '.css';
        $url = $baseUrl . $fileName;
        $localPath = $localDir . $fileName;
        
        // Skip if file already exists
        if (file_exists($localPath)) {
            echo "Skipping {$fileName} (already exists)\n";
            continue;
        }
        
        echo "Downloading {$fileName}... ";
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && $content !== false) {
            // Replace CDN paths in CSS content
            $content = str_replace('https://cdn.strixmedia.ru/assets', '../img', $content);
            $content = str_replace('https://cdn.trustindex.io/assets', '../img', $content);
            $content = str_replace('cdn.strixmedia.ru/assets', '../img', $content);
            $content = str_replace('cdn.trustindex.io/assets', '../img', $content);
            
            file_put_contents($localPath, $content);
            echo "OK\n";
            $downloaded++;
        } else {
            echo "FAILED (HTTP {$httpCode})\n";
            $failed++;
        }
    }
}

// Download ti-preview-box.css
echo "\nDownloading ti-preview-box.css... ";
$previewUrl = 'https://cdn.strixmedia.ru/assets/ti-preview-box.css';
$previewLocalPath = __DIR__ . '/static/css/ti-preview-box.css';

if (!file_exists($previewLocalPath)) {
    $ch = curl_init($previewUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $content = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 && $content !== false) {
        // Replace CDN paths in CSS content
        $content = str_replace('https://cdn.strixmedia.ru/assets', 'img', $content);
        $content = str_replace('https://cdn.trustindex.io/assets', 'img', $content);
        
        file_put_contents($previewLocalPath, $content);
        echo "OK\n";
        $downloaded++;
    } else {
        echo "FAILED (HTTP {$httpCode})\n";
        $failed++;
    }
} else {
    echo "Skipping (already exists)\n";
}

echo "\n\nDownload complete!\n";
echo "Downloaded: {$downloaded} files\n";
echo "Failed: {$failed} files\n";
