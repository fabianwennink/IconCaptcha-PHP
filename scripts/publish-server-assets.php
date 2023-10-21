<?php

/**
 * Script to publish server assets from the IconCaptcha package.
 * This script copies files from the vendor package to a destination directory.
 */

$assetsDir = __DIR__ . "/../assets";
$iconsDir = "$assetsDir/icons";
$placeholderImage = "$assetsDir/placeholder.png";
$destinationDir = __DIR__ . '/../../../../iconcaptcha';
$iconsDestination = "$destinationDir/icons";
$placeholderImageDestination = "$destinationDir/placeholder.png";

if (is_dir($assetsDir)) {

    // Include the required helper functions.
    require_once 'publish-helpers.php';

    // Copy the icons from the package to the project.
    makeDirectory($iconsDestination);
    recursiveCopy($iconsDir, $iconsDestination);

    // Copy the placeholder image from the package to the project.
    copy($placeholderImage, $placeholderImageDestination);

    echo "IconCaptcha server assets published successfully.\n";
} else {
    echo "IconCaptcha assets directory not found.\n";
}


