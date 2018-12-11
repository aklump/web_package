<?php

/**
 * @file
 * Hook runner for PHP hooks.
 *
 * The file will run a single hook file as passed in $argv[1].
 */

use AKlump\LoftLib\Bash\Bash;
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
  $hook_file = FilePath::create(realpath($argv[1]));
  array_splice($argv, 1, 1);
  $build = new HookService(
    FilePath::create(__DIR__ . '/..'),
    FilePath::create($argv[9]),
    FilePath::create($argv[7]),
    $argv[3],
    $argv[4],
    $argv[2],
    $argv[1],
    $argv[6],
    $argv[5],
    $argv[8]
  );

  // Capture output so we can write to a tree below.
  ob_start();
  switch ($hook_file->getExtension()) {
    // Politely remind users to remove.
    case 'txt':
    case 'md':
      throw new HookException("This file is not a hook, please delete or move it: " . $hook_file->getPath());
      break;

    case 'php':

      // Include our provided globals.
      require_once __DIR__ . '/wp_functions.php';

      // Include a bootstrap file defined in the project using WP.
      $local_include = $argv[13] . '/bootstrap.php';
      if (file_exists($local_include)) {
        require_once $local_include;
      }
      require $hook_file->getPath();
      break;

    case 'sh':
      $hook_script_args = $argv;
      array_shift($hook_script_args);

      // Include our provided globals.
      $sources[] = __DIR__ . '/wp_functions.sh';
      $sources[] = $argv[13] . '/bootstrap.sh';
      $sources[] = $hook_file->getPath();
      $sources = 'source ' . implode('; source ', array_filter($sources, 'file_exists'));
      try {
        print Bash::exec($sources, $hook_script_args);
      }
      catch (\Exception $exception) {
        switch ($exception->getCode()) {
          case 1:
            throw new HookException($exception->getMessage());
            break;
          default:
            throw new BuildFailException($exception->getMessage());
            break;
        }
      }
      break;

    default:
      throw new BuildFailException("Unsupported hook file type: *.{$hook_file->getExtension()}");
  }
}
catch (HookException $exception) {
  echo Color::wrap("yellow", $exception->getMessage());
}
catch (\Exception $exception) {
  echo Color::wrap("red", $exception->getMessage());
  $exit_status = 1;
}
catch (\Error $exception) {
  echo Color::wrap("red", (string) $exception);
  $exit_status = 1;
}
$output = array_filter(explode(PHP_EOL, trim(ob_get_contents())));
if (ob_get_contents()) {
  ob_end_clean();
}
$output = empty($output) ? ['OK'] : $output;
echo Output::tree($output);
exit($exit_status);
