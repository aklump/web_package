<?php
/**
 * @file
 * Update the version string in a shell script: script.sh
 *
 */
$filename = $argv[7] . '/script.sh';

if ($contents = $before = file_get_contents($filename)) {

  // Do a regex replace of the version declaration in code.
  $contents = preg_replace('/^script_version=[\d\.]+/m', 'script_version=' . $argv[2], $contents);
  $changed = $contents !== $before;
  if ($changed && file_put_contents($filename, $contents)) {
    echo "Version string updated to " . $argv[2] . " in $filename";
    return;
  }
}

if ($changed) {
  echo "Error updated version string in $filename.";
}
