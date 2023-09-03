<?php

/**
 * @file
 * Load a source file, replace tokens and save to dist folder, then minify.
 */

namespace AKlump\WebPackage;

$build
  ->loadFile('src/smart-images.js')
  ->replaceTokens()
  ->saveToDist()
  ->minifyFile('dist/smart-images.js')
  ->displayMessages();
