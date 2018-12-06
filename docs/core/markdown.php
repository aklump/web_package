<?php

/**
 * @file
 * Run the markdown compiler
 *
 * @in group loft_docs
 * @{
 */
require_once dirname(__FILE__) . '/vendor/autoload.php';

use AKlump\LoftDocs\MarkdownExtra;
use Webuni\FrontMatter\FrontMatter;

$in_file = $argv[1];
$out_file = $argv[2];

if (is_file($in_file) && ($contents = file_get_contents($in_file))) {
  $fm = new FrontMatter();
  $document = $fm->parse($contents);
  $contents = $document->getContent();
  $data = $document->getData();

  if (isset($data['twig'])) {
    foreach ($data['twig'] as $find => $replace) {
      $data['tokens']["{{ $find }}"] = $replace;
    }
  }

  // If the tokens frontmatter key is present then we need to perform a token replace.
  if (isset($data['tokens'])) {
    uksort($data['tokens'], function ($a, $b) {
      $a = strlen($a);
      $b = strlen($b);

      return $b - $a;
    });
    foreach ($data['tokens'] as $find => $replace) {
      $contents = str_replace($find, $replace, $contents);
    }
  }

  $my_html = MarkdownExtra::defaultTransform($contents);
  file_put_contents($out_file, $my_html);
}
