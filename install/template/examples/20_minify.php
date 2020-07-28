<?php

/**
 * @file
 * Load a source file, replace tokens and save to dist folder.
 */

namespace AKlump\WebPackage;

$build
  ->setDistributionDir('dist')
  ->minifyFile('LoftImages.js')
  ->loadFile('LoftImages.min.js')
  ->saveToDist()
  ->displayMessages();
