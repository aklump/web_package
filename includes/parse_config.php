<?php
/**
 * @deprecated
 */

use AKlump\WebPackage\Config\ConfigManager;
use AKlump\WebPackage\Config\Loader\LegacyLoader;
use AKlump\WebPackage\Config\Loader\YamlLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

require __DIR__ . '/../vendor/autoload.php';

/**
 * @var string A relative or absolute path to the config file to be parsed.
 * Relative paths are resolved to the CWD.
 */
$path_to_load = $argv[1];

$filesystem = new Filesystem();
if (!$filesystem->isAbsolutePath($path_to_load)) {
  $path_to_load = Path::makeAbsolute($path_to_load, getcwd());
}
$manager = new ConfigManager();
$config = $manager->loadFile($path_to_load);

// Convert array to BASH eval code.
$eval = [];
foreach ($config as $key => $value) {
  $eval[] = "wp_$key=\"$value\"";
}

// Create a string of eval code for BASH in the original format.
echo implode(PHP_EOL, $eval) . PHP_EOL;
exit(0);
