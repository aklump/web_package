<?php
/**
 * @file
 * Reads the filesystem and writes new .json format as a file.
 *
 */
$file = __FILE__;
require_once dirname($file) . '/../vendor/autoload.php';

if (count($argv) < 3
  || ((list(,$source_dir, $json_file) = $argv)
    && (empty($source_dir) || empty($json_file)))) {
  echo "Missing parameters to $file" . PHP_EOL;
  return;
}

if (file_exists($json_file)) {
  echo "Cannot create $json_file as it already exists." . PHP_EOL;
  return;
}

$info = array();
$first_level = scandir($source_dir);
foreach ($first_level as $file) {
  if (substr($file, 0, 1) === '.') {
    continue;
  }

  // Do not include search--results.md
  if ($file === 'search--results.md') {
    continue;
  }

  // We check for chapter--section.md format
  $chapter = '';
  $section = $file;
  if (($parts = explode('--', $section)) && count($parts) > 1) {
    $chapter = array_shift($parts);
    $section = implode('', $parts);
  }

  // In the top level there is no chapter indication.
  if (path_is_section($section)) {
    $info[pathinfo($section, PATHINFO_FILENAME)] = array(
      'file' => $file,
      'title' => clean_title($section),
      'parent' => $chapter,
    );
  }

  // One level in, designates a chapter by dirname.
  // elseif (is_dir($source_dir . '/' . $section)) {
  //   $chapter_level = scandir($source_dir . '/' . $section);
  //   foreach ($chapter_level as $chapter_section) {
  //     if (substr($chapter_section, 0, 1) === '.') {
  //       continue;
  //     }

  //     if (path_is_section($chapter_section)) {
  //       $info[pathinfo($chapter_section, PATHINFO_FILENAME)] = array(
  //         'file' => $section . '/' . $chapter_section,
  //         'title' => clean_title($chapter_section),
  //         'parent' => clean_id($section),
  //       );
  //     }
  //   }
  // }
}

require_once dirname(__FILE__) . '/json.inc';

