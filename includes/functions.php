<?php
/**
 * Return a string representation of a key/value array.
 *
 * @param $info
 *
 * @return string
 */
function wp_key_value_chart($info) {
  $build = array();
  $build[] = '';
  foreach ($info as $key => $value) {
    if (is_array($value)) {
      $value = json_encode($value);
    }
    $build[] = str_pad($key, 20, ' ') . ": $value";
    $build[] = '';
  }
  $build[] = '';

  return implode(PHP_EOL, $build) . PHP_EOL;
}
