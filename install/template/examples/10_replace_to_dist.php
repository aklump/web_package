<?php

/**
 * @file
 * An example PHP hook file.
 */

namespace AKlump\WebPackage;

$build
  ->load('src/smart-images.js')
  ->replace()
  ->saveToDist()
  ->displayMessages();
