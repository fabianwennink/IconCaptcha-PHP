<?php

/*
 * IconCaptcha - Copyright 2023, Fabian Wennink (https://www.fabianwennink.nl)
 * Licensed under the MIT license: https://www.fabianwennink.nl/projects/IconCaptcha/license
 *
 * The above copyright notice and license shall be included in all copies or substantial portions of the software.
 */

/**
 * Will attempt to create a new directory at the given path, if none already exists.
 * @param string $directory The directory to make.
 */
function makeDirectory(string $directory): void {
    if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
        throw new \RuntimeException(sprintf('Publish destination directory "%s" was not created.', $directory));
    }
}

/**
 * Recursively copies the content of the source directory to the destination path.
 * @param string $source Source directory to copy.
 * @param string $destination Destination directory to which the source should be copied.
 */
function recursiveCopy(string $source, string $destination): void {
    $iterator = new DirectoryIterator($source);
    foreach ($iterator as $fileInfo) {
        if (!$fileInfo->isDot()) {
            $sourcePath = $fileInfo->getPathname();
            $destinationPath = $destination . '/' . $fileInfo->getFilename();

            // If it's a directory, recursively copy its contents.
            // Otherwise, simply copy it to the destination.
            if ($fileInfo->isDir()) {
                makeDirectory($destinationPath);
                recursiveCopy($sourcePath, $destinationPath);
            } else {
                copy($sourcePath, $destinationPath);
            }
        }
    }
}
