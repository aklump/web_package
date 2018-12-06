<?php

/**
 * @file
 * Run the automated tests.
 */

namespace AKlump\WebPackage;

$build
  ->runTests('phpunit.xml')
  ->displayMessages();
