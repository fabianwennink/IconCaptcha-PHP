<?php

/**
 * Script to publish client assets from the IconCaptcha package.
 * This script copies files from the vendor package to a destination directory.
 */

$assetsDir = __DIR__ . "/../assets/client";
$destinationDir = __DIR__ . '/../../../../iconcaptcha/client';

if (is_dir($assetsDir)) {

    // Include the required helper functions.
    require_once 'publish-helpers.php';

    // Copy the client assets from the package to the project.
    makeDirectory($destinationDir);
    recursiveCopy($assetsDir, $destinationDir);

    echo "IconCaptcha client assets published successfully.\n";
} else {
    echo "IconCaptcha client assets directory not found.\n";
}


