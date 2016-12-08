<?php
/**
 * @file
 * Update bower.json from *.info
 */
try {
  $bower = $argv[7] . '/bower.json';
  if (!file_exists($bower)) {
    throw new \RuntimeException("bower.json not found!");
  }

  // Replace bower components.
  // note: name has to be snake case.
  $data              = json_decode(file_get_contents($bower));
  $data->version     = $argv[2];
  $data->homepage    = $argv[5];
  $data->authors     = array($argv[6]);
  $data->description = $argv[4];

  // Write teh new bower file.
  $contents = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
  file_put_contents($bower, $contents);

  print $bower . " has been updated.";

} catch (Exception $e) {
  print $e->getMessage();
}
