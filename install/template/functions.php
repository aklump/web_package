<?php
/**
 * @file
 * Provides functions for build scripts
 *
 * @ingroup web_package
 * @{
 */

/**
 * @deprecated Use cp_with_token_replacement instead.
 */
function js_replace_name_version(&$text, $package_name, $new_version) {
  $regex = '/(.*?)(\s+(?:JQuery|JavaScript|JS).*v)(\{\{\s*version\s*\}\}|[\d\.a-z\-]+)$/i';
  $text = preg_replace($regex, ' * ' . $package_name . '${2}' . $new_version, $text);
}

/**
 * @deprecated Use cp_with_token_replacement instead.
 */
function js_replace_description(&$text, $description) {
  $text = " * $description";
}

/**
 * @deprecated Use cp_with_token_replacement instead.
 */
function js_replace_date(&$text, $date) {
  $text = " * Date: $date";
}

/**
 * @deprecated Use cp_with_token_replacement instead.
 */
function js_replace_homepage(&$text, $homepage) {
  $text = " * $homepage";
}

/**
 * Replace the copyright year and holder in a string.
 *
 * @param  string &$text The source string.
 * @param  string $holder The name of the copyright holder.
 * @param  string $year Optional, defaults to 'now' for the current year.
 *
 * @deprecated Use cp_with_token_replacement instead.
 */
function js_replace_copyright(&$text, $holder, $year = 'now') {
  $regex = '/copyright\s*((\d{4})(?:\-(\d{4}))?),\s*(.*)$/i';
  if (preg_match($regex, $text, $matches)) {
    list(, $find_date, $original_date, , $find_name) = $matches;
    $replace_date[] = $original_date;

    if ($year === 'now') {
      $now = new \DateTime('now', new \DateTimeZone('America/Los_Angeles'));
      $year = $now->format('Y');
    }
    $replace_date[] = $year;

    $replace_date = implode('-', array_unique($replace_date));
    $text = str_replace($find_date, $replace_date, $text);

    $text = str_replace($find_name, $holder, $text);
  }
}

/**
 * @deprecated Use cp_with_token_replacement instead.
 */
function js_replace_version_function(&$source, $new_version) {
  $source = preg_replace('/((?:this|\$\.fn\..+)\.version.+(?:\'|"))([\d\.]+?)((?:\'|").*)/s', '${1}' . $new_version . '${3}', $source);
}

/**
 * Copy files from root into demo.
 *
 * @param  array $files
 * @param  string $demo_dir
 */
function to_demo_copy($path_to_root, $files, $demo_dir = 'demo') {
  foreach ($files as $file) {
    copy($path_to_root . "/$file", $path_to_root . "/$demo_dir/$file");
  }
}

/**
 * Copy files from demo into root.
 *
 * @param  array $files
 * @param  string $demo_dir
 */
function from_demo_copy($path_to_root, $files, $demo_dir = 'demo') {
  foreach ($files as $file) {
    copy($path_to_root . "/$demo_dir/$file", $path_to_root . "/$file");
  }
}

/**
 * Copy file from point a to point b, replacing tokens.
 *
 * This should be used to merge in the web_package values (version, name, etc.)
 * from a source file to a build or dist file, such as with a jQuery plugin.
 *
 * @param string $source_path
 *   Set this to the source file , e.g. "src/file.js".
 * @param string $output_parent_dir
 *   Set this to the destination file parent directory, e.g. "dist".  The
 *   basename is taken from $source_path.  Defaults to "dist".s
 * @param array $additional_token_map
 *   Extra tokens can be added here.  Tokens should begin with double
 *   underscore, e.g. "__some_token", that is their keys.  Values should be
 *   what tokens get replaced with.  Be aware that the core tokens are already
 *   generated for you:
 *     __version
 *     __name
 *     __title (only if present in info file.)
 *     __description (wrapped to 75 chars)
 *     __date
 *     __author
 *     __homepage
 *     __year or NNNN_year, e.g. 2013__year, replaces with 2013-2018
 *   This is optional.
 *
 * @throws \InvalidArgumentException
 * @throws \RuntimeException
 *
 * @code
 *   try {
 *     cp_with_token_replacement($argv[7] . '/src/smart-images.js');
 *   }
 *   catch (\Exception $exception) {
 *     echo $exception->getMessage();
 *     exit(1);
 *   }
 *   exit(0);
 * @endcode
 */
function cp_with_token_replacement($source_path, $output_parent_dir = 'dist', array $additional_token_map = array()) {

  // Validate the output dir and file.
  if (!is_dir($output_parent_dir)) {
    throw new \InvalidArgumentException("\"$output_parent_dir\" is not an existing directory; create it first.");
  }
  $output_path = realpath($output_parent_dir) . '/' . basename($source_path);
  $source_path = realpath($source_path);
  if (file_exists($output_path)) {
    throw new \InvalidArgumentException("\"$output_path\" already exists; it must not exist.");
  }
  if ($output_path === $source_path) {
    throw new \RuntimeException("Output may not be the same file as source.");
  }

  global $argv;
  list(
    $this_file,
    $prev_version,
    $new_version,
    $package_name,
    $description,
    $homepage,
    $author,
    $path_to_root,
    $date,
    $path_to_info
    ) = $argv;

  $token_map = $additional_token_map;
  $token_map += array(
    '__version' => $new_version,
    '__name' => $package_name,
    '__description' => wordwrap($description, 75),
    '__date' => $date,
    '__author' => $author,
    '__homepage' => $homepage,
  );

  $info = json_decode(file_get_contents($argv[9]), TRUE);
  if (isset($info['title'])) {
    $token_map['__title'] = $info['title'];
  }
  $code = file_get_contents($source_path);
  $code = str_replace(array_keys($token_map), array_values($token_map), $code);

  // Replace the year which will appear as '__year' or '2015__year'.
  $code = preg_replace_callback('/(\s\d{2,4}?)__year/', function ($matches) {
    $years = array();
    isset($matches[1]) && $years[] = $matches[1];
    $years[] = date('Y');

    return implode('-', $years);
  }, $code);

  if (!file_put_contents($output_path, $code)) {
    throw new \RuntimeException("Could not write to \"$output_path\".");
  }
}
