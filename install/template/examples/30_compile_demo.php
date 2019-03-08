<?php

/**
 * @file
 * Compile the Photo Essay demo.
 */

namespace AKlump\WebPackage;

$build
    ->setDemoSource('documentation/demo')
    ->addToDemo('../../dist/photo_essay.css')
    ->addToDemo('../../dist/photo_essay_style.css')
    ->addToDemo('../../dist/jquery.photo_essay.js')
    ->addToDemo('../../node_modules/bootstrap/dist/css/bootstrap.min.css')
    ->addToDemo('../../node_modules/bootstrap/fonts')
    ->addToDemo('../../node_modules/jquery/dist/jquery.js')
    ->generateDemoTo('docs')
    ->displayMessages();
