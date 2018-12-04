<?php

/**
 * @file
 * Hook runner for PHP hooks.
 *
 * The file will run a single hook file as passed in $argv[1].
 */

use AKlump\LoftLib\Bash\Color;
use AKlump\LoftLib\Bash\Output;
use AKlump\LoftLib\Storage\FilePath;
use AKlump\WebPackage\BuildFailException;
use AKlump\WebPackage\HookException;
use AKlump\WebPackage\HookService;

require_once __DIR__ . '/../vendor/autoload.php';

try {
  $exit_status = 0;
  $output = [];
  $path_to_hook = realpath($argv[1]);
  array_splice($argv, 1, 1);
  $build = new HookService(
    FilePath::create($argv[9]),
    $argv[7],
    $argv[3],
    $argv[4],
    $argv[2],
    $argv[1],
    $argv[6],
    $argv[5],
    $argv[8]
  );

  // Include our provided globals.
  require_once __DIR__ . '/wp_functions.php';

  // Include a bootstrap file defined in the project using WP.
  $local_include = $argv[13] . '/bootstrap.php';
  if (file_exists($local_include)) {
    require_once $local_include;
  }

  // Capture output so we can write to a tree below.
  ob_start();
  require $path_to_hook;
  $output = explode(PHP_EOL, ob_get_contents());
  ob_end_clean();
}
catch (HookException $exception) {
  $output[] = Color::wrap("yellow", $exception->getMessage());
}
catch (BuildFailException $exception) {
  $output[] = Color::wrap("red", $exception->getMessage());
  $exit_status = 1;
}
catch (\Error $exception) {
  $output[] = Color::wrap("red", (string) $exception);
  $exit_status = 1;
}
echo Output::tree($output);
exit($exit_status);
