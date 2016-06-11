<?php
/**
 * @file
 * Adds search to the output
 *
 */
namespace AKlump\LoftDocs;

require_once dirname(__FILE__) . '/../vendor/autoload.php';

list(,$outline, $core_dir, $build_dir, $webpage_dir) = $argv;

$outline = load_outline($outline);

// Search support
if (!empty($outline['settings']['search'])) {
  $search_dir = "$webpage_dir/search";

  $hook_file  = $core_dir . "/includes/webpage." . $outline['settings']['search'] . ".sh";
  if (is_file($hook_file)) {
    system("$hook_file $core_dir $search_dir");
  }

  // Now create the search contents
  $searchBuilder = new TipueSearch;

  $source_files = array_filter(scandir($build_dir . '/html/'), function($value) {
    return preg_match('/\.html$/', $value);
  });

  try {
    foreach ($source_files as $html_file) {
      $searchHtml = new SearchHtml($build_dir . '/html/' . $html_file, TRUE);
      $data       = $searchHtml->getData();
      $searchBuilder->addPage($data);
    }
    $searchBuilder->createFile($search_dir, TRUE);
  } catch (\Exception $e) {
    print $e;
  }
}

