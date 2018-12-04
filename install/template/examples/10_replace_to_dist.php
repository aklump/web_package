<?php

/**
 * @file
 * Load a source file, replace tokens and save to dist folder.
 */

namespace AKlump\WebPackage;

$build
  ->load('src/smart-images.js')
  ->replace()
  ->saveToDist()
  ->displayMessages();
