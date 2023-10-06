<?php

/**
 * @file
 * Run the automated tests.
 */

namespace AKlump\WebPackage;

$which_php = array_reverse(glob('/Applications/MAMP/bin/php/php7.4*'))[0] . '/bin/php';
$build
  ->setPhp($which_php)
  ->runTests('phpunit.xml')
  ->displayMessages();
