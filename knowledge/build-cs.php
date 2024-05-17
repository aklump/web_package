<?php

/**
 * @file
 * An hook example of generating a PHP class method cheatsheet.
 *
 * Available variables:
 * - $compiler.
 */

use AKlump\LoftLib\Code\Markdown;
use AKlump\LoftDocs\DynamicContent\PhpClassMethodReader;


// Then you need to include an autoloader for the classes you want to scan.
$app_root = realpath($GLOBALS['book_path'] . '/../');
require_once $app_root . '/vendor/autoload.php';

// Define the classes to read.
$reader = new PhpClassMethodReader();
$reader->addClassToScan('\AKlump\WebPackage\HookService', [

  // But we want to exclude the method called 'getBrowser', so we use the
  // second parameter which defines a filter.
  PhpClassMethodReader::EXCLUDE,
  ['/^__/'],
]);

// Convert the scanned data into a markup table for each group, in this
// example there is only one group, because we are using only one class.
foreach ($reader->scan() as $group => $methods) {
  $contents = '';
  $methods = array_map(function ($method) use ($group) {
    return [$group => '<strong>' . $method['name'] . '</strong> <em>(' . implode(', ', $method['params']) . ')</em>'];
  }, $methods);
  $contents .= Markdown::table($methods) . PHP_EOL;

  // Save the snippet to be used by other pages.
  $compiler->addInclude("_{$group}.md", $contents);
}
