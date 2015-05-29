<?php
/**
 * @file Copies the *.info data over the top of the composer.json file.
 *
 * Name is not updated because composer.name means something different than
 * web_package.name--rather wp.name is prefixed onto the composer.description.
 *
 * Author data will only be overwritten if the name matches, otherwise a new
 * author item will be added to the array.
 *
 * Version will only be copied if version is not null in composer.json.
 */
$composer = $argv[7] . '/composer.json';
$info     = $argv[9];

// Load and parse both files.
if (!($json = $before = json_decode(file_get_contents($composer)))
  || !($info = parse_ini_file($info))) {
  throw new Exception("Can't load info files.");
}

// Begin updating the json of composer.json.
$json->description = implode(': ', array($info['name'], $info['description']));
$json->homepage    = $info['homepage'];

// https://getcomposer.org/doc/04-schema.md#version
if (isset($json->version)) {
  $json->version = $info['version'];
}

// Pull the author name from email address
preg_match('/(.*?)\s*<(.*?)>/', $info['author'], $author);
$author += array('', '');
$found = FALSE;
foreach ($json->authors as $key => $item) {
  if ($item->name === $author[1] && $found = 1 && isset($author[2])) {
    $json->authors[$key]->email = $author[2];
    break;
  }
}
if (!$found) {
  $json->authors[] = (object) array(
    'name' => $author[1],
    'email' => $author[2],
  );
}

// Update composer.json file.
if (($json = json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))
  && $json !== $before
  && !file_put_contents($composer, $json)) {
  throw new Exception("Could not write composer.json");
}