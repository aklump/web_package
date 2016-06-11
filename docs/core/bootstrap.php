<?php
/**
 * @file
 * Boostrapper and function declarations
 *
 * @ingroup loft_docs
 * @{
 */

/**
 * Parse all todos in a string
 *
 * @param  string $string Looking for this pattern  '- [ ] @todo...EOL'
 * @param  string $prefix A prefix to the todo item; used to include the file
 * name in the todo item.
 *
 * @return array         
 */
function parse_todos($string, $prefix = '') {
  $todos = array();
  if (is_string($string)
    //&& preg_match_all('/- \[ \] @todo.*$/m', $string, $matches)) {
    && preg_match_all('/- \[ \] .*$/m', $string, $matches)) {
    
    if (!empty($prefix) || !empty($remove_todo)) {
      foreach (array_keys($matches[0]) as $key) {

        if ($prefix) {
          $matches[0][$key] = str_replace('- [ ] ', "- [ ] $prefix", $matches[0][$key]);
        }
      }
    }    
    $todos = $matches[0];
  }

  return $todos;
}

/**
 * Flattens an array of todo items into a string
 *
 * @param  array $array
 *
 * @return string        
 */
function flatten_todos($array) {
  if (is_array($array)) {
    return implode("\n", array_filter($array)) . "\n";
  }
}

/**
 * Sort an array of todo items by @w flag
 *
 * @param  array &$todos
 */
function sort_todos(&$todos) {
  if (is_array($todos)) {
    usort($todos, '_sort_todos');
    $todos = array_values(array_filter(array_unique($todos)));
  }
}

/**
 * Helper for usort
 */
function _sort_todos($a, $b) {
  if (get_weight($a) === get_weight($b)) {
    return 0;
  }

  return get_weight($a) > get_weight($b) ? 1 : -1;
}

/**
 * Return the numeric weight of a todo item
 *
 * @param  string $string
 *
 * @return int||float
 */
function get_weight($string) {
  if (preg_match_all('/@w([\d\.]+)/', $string, $matches)) {
    return 1 * end($matches[1]);
  }

  return 0;
}

/**
 * Returns a string read to be used as an id.
 *
 * @param  string $text
 *
 * @return string
 */
function clean_id($text) {
  $text = preg_replace(get_markdown_extensions(TRUE), '', $text);
  return strtolower($text);
}

/**
 * Return the recognized markdown extensions (without the dot).
 *
 * @param  boolean $regex True for a regex expression.
 *
 * @return array|string
 */
function get_markdown_extensions($regex = FALSE) {
  $ext = array('md', 'mdown', 'markdown');
  return $regex ? '/\.(' . implode('|', $ext) . ')$/' : $ext;
}
/**
 * Returns a string to be used as a chapter/section title.
 *
 * @param  string $text
 *
 * @return string
 */
function clean_title($text) {
  $text = preg_replace(get_markdown_extensions(TRUE), '', $text);
  
  return ucwords(preg_replace('/[-_]/', ' ', $text));
}

/**
 * Returns the markdown file extention for a filename
 *
 * @param  string $file This is the filename relative to the source directory without extension.
 *
 * @return string
 */
function get_md_source_file_extension($file) {
  //@todo make this dynamic?
  return '.md';
}

/**
 * Detect if a filename points to a markdown file.
 *
 * @param  string $path
 *
 * @return bool
 */
function path_is_section($path) {
  $ext   = pathinfo(strtolower($path), PATHINFO_EXTENSION);
  $valid = get_markdown_extensions();
  //@todo want to be able to show text files?
  // return in_array($ext, array('txt') + get_markdown_extensions());
  
  return in_array($ext, $valid);
}

/**
 * Loads the outline json file and fills in missing defaults.
 *
 * @param  string $outline_file
 *
 * @return array
 */
function load_outline($outline_file) {
  $outline = array();

  if (file_exists($outline_file)) {
    $outline = json_decode(file_get_contents($outline_file), TRUE);
    $outline += array(
      'appendices' => array(),
      'authors'    => array(),
      'chapters'   => array(),
      'cover'      => array(),
      'sections'   => array(),
      'settings'   => array('search' => 'tipuesearch'),
    );
  }

  return $outline;

}
