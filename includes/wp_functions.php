<?php
/**
 * @file
 * Provides functions for build scripts.
 *
 * This is autoloaded before PHP hook scripts by hook_runner.php.  All
 *   functions herein should be prefixed with "wp_".
 *
 * @ingroup web_package
 * @{
 */

/**
 * Copy files from root into demo.
 *
 * @param array $files
 * @param string $demo_dir
 */
function to_demo_copy($path_to_root, $files, $demo_dir = 'demo') {
  foreach ($files as $file) {
    copy($path_to_root . "/$file", $path_to_root . "/$demo_dir/$file");
  }
}

/**
 * Copy files from demo into root.
 *
 * @param array $files
 * @param string $demo_dir
 */
function from_demo_copy($path_to_root, $files, $demo_dir = 'demo') {
  foreach ($files as $file) {
    copy($path_to_root . "/$demo_dir/$file", $path_to_root . "/$file");
  }
}
