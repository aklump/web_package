<?php
/**
 * @file
 * Run the markdown compiler
 *
 * @in group loft_docs
 * @{
 */
require_once dirname(__FILE__) . '/vendor/autoload.php';

use \AKlump\LoftDocs\MarkdownExtra;

$in_file  = $argv[1];
$out_file = $argv[2];

if ($contents = file_get_contents($in_file)) {
  $my_html = MarkdownExtra::defaultTransform($contents);
  file_put_contents($out_file, $my_html);
}
