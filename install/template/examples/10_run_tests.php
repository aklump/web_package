<?php

/**
 * @file
 * Run the automated tests.
 */

namespace AKlump\WebPackage;

$build
    ->setPhp('/Applications/MAMP/bin/php/php5.6.32/bin/php')
    ->runTests('phpunit.xml')
    ->displayMessages();
