<?php
/**
 * @file
 * Parses Drupal's Advanced Help .ini file and creates page var .kit variables
 *
 * @ingroup loft_docs
 * @{
 */
use AKlump\LoftDocs\OutlineJson as Index;

require_once dirname(__FILE__) . '/../vendor/autoload.php';

$outline = load_outline($argv[1]);
$index   = new Index($outline);

$vars = array(
  'classes' => array(),
);
if (($data = $index->getData()) && isset($data[$argv[2]])) {
  $vars = $data[$argv[2]];
  $vars['classes'] = array('page-' . $vars['id']);
}
$declarations = array();
$vars['classes'] = implode(' ', $vars['classes']);
foreach ($vars as $key => $value) {
  $declarations[] = "\$$key = $value";
}

// Add in additional kit vars:
$now = new \DateTime('now', new \DateTimeZone('America/Los_Angeles'));
$declarations[] = '$date = ' . $now->format('r');

$declarations[] = '$version = ' . $argv[3];

// Search support
if (!empty($outline['settings']['search'])) {
  $declarations[] = '$search = true';
  if ($argv[2] === 'search--results') {
    $declarations[] = '$search_results_page = true';
  }
  else {
    $declarations[] = '$search_results_page = false';
  }
}

// Now write the vars
print '<!--' . implode("-->\n<!--", $declarations) . "-->\n";

/** @} */ //end of group: loft_docs
