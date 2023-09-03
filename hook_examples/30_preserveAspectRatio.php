<?php
// List out all files which need to have the tag added, with the value of the tag.
$files = array(
  $argv[7] . '/images/comment-bubble.svg' => 'none',
);
foreach ($files as $file => $value) {
  if (file_exists($file) && ($xml = simplexml_load_file($file))) {
    $xml->addAttribute('preserveAspectRatio', $value);
    $xml->asXml($file);
  }
}
