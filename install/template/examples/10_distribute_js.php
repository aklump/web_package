<?php

/**
 * @file
 * Load a source file, replace tokens and save to dist folder, then minify.
 */

namespace AKlump\WebPackage;

$build
  ->load('src/smart-images.js')
  ->replace()
  ->saveToDist()
  ->minify('dist/smart-images.js')
  ->displayMessages();
