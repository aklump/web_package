<?php

/**
 * @file
 * Generates documentation, adjusts paths and adds to SCM.
 */

namespace AKlump\WebPackage;

$build
  ->setDocumentationSource('documentation')
  ->generateDocumentationTo('docs')
  // This will adjust the path to the image, pulling it from docs.
  ->loadFile('README.md')
  ->replaceTokens([
    'images/smart-images' => 'docs/images/smart-images',
  ])
  ->saveReplacingSourceFile()
  // Add some additional files to SCM that were generated and outside of the docs directory.
  ->addFilesToScm([
    'README.md',
    'CHANGELOG.md',
  ])
  ->displayMessages();
