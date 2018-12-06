<?php

/**
 * @file
 * Load a source file, replace tokens and save to dist folder.
 */

namespace AKlump\WebPackage;

$build
  ->addFilesToScm([
    "docs",
    "help",
    "README.txt",
    "README.md",
  ])
  ->displayMessages();
