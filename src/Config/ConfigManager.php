<?php

namespace AKlump\WebPackage\Config;

use AKlump\WebPackage\Config\Loader\LegacyLoader;
use AKlump\WebPackage\Config\Loader\YamlLoader;
use AKlump\WebPackage\VersionScribeInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Filesystem\Filesystem;
use Exception;
use Symfony\Component\Filesystem\Path;

class ConfigManager {

  public function __construct() {
    $this->filesystem = new Filesystem();
  }

  /**
   * @param string $path_to_load
   *   The extension will be ignored and the basename used against all supported
   *   extensions.  See $fifo_files below for precendance.
   *
   * @return array
   *   The loaded configuration from the single file.
   * @throws \InvalidArgumentException If $path_to_load is not absolute.
   */
  public function loadFile(string $path_to_load): array {
    $located_file = $this->locateFile($path_to_load);
    if (!$located_file) {
      return [];
    }

    $locator = new FileLocator([dirname($path_to_load)]);
    $loader_resolver = new LoaderResolver(
      [
        new LegacyLoader($locator),
        new YamlLoader($locator),
      ]
    );
    $loader = new DelegatingLoader($loader_resolver);

    return $loader->load($located_file);
  }

  /**
   * Get the exact filepath (with extension) of loadable config file.
   *
   * @param string $path_to_load
   *
   * @return string
   *   The absolute path with extension to the file that will be loaded by
   *   self::loadFile($path_to_load).
   */
  public function locateFile(string $path_to_load): string {
    if (!Path::isAbsolute($path_to_load)) {
      throw new \InvalidArgumentException(sprintf('$path_to_load must be absolute; "%s" is not', $path_to_load));
    }
    $locator = new FileLocator([dirname($path_to_load)]);

    // The first file found will be used and the rest skipped.
    $fifo_files = [
      basename($path_to_load) . '.yml',
      basename($path_to_load) . '.yaml',
      basename($path_to_load),
    ];
    foreach ($fifo_files as $fifo_file) {
      try {
        $located_file = $locator->locate(basename($fifo_file));
      }
      catch (Exception $e) {
        continue;
      }
    }

    return $located_file ?? '';
  }

  public function handleGitRoot(array &$config, string $start_dir): void {
    if (empty($config['git_root'])) {
      $path = $start_dir;
      while ($path && $path !== '/' && !file_exists("$path/.git")) {
        $path = dirname($path);
      }
      if (file_exists("$path/.git")) {
        $config['git_root'] = Path::makeRelative("$path", $start_dir);
        if ('' === $config['git_root']) {
          $config['git_root'] = '.';
        }
      }
    }
  }

  public function migrateOldKeys(array &$config): void {
    if (isset($config['micro'])) {
      $config['patch'] = $config['micro'];
      unset($config['micro']);
    }
  }

  public function handleVersionFile(array &$config, string $root_dir): void {
    $config[Config::VERSION_FILE] = $config[Config::VERSION_FILE] ?? ConfigDefaults::VERSION_FILE;
    if (empty($config[Config::VERSION_FILE])) {
      return;
    }

    $version_file =& $config[Config::VERSION_FILE];
    if (strstr($version_file, '*') !== FALSE) {
      $info = pathinfo($version_file);
      $temp = array_filter($info, function ($value) {
        return strstr($value, '*') !== FALSE;
      });
      if (array_diff_key($temp, array_flip(['basename', 'filename']))) {
        throw new \RuntimeException(sprintf('version_file (%s) is invalid, you may only glob the filename, e.g. /foo/bar/*.yml', $version_file));
      }

      //... but we have to de-glob-ify!
      $version_file = str_replace('*.', VersionScribeInterface::DEFAULT_FILENAME . '.', $version_file);
    }

    if (!Path::isAbsolute($version_file)) {
      $version_file = Path::makeAbsolute($version_file, $root_dir);
    }

    if (!file_exists($version_file)) {
      $this->filesystem->touch($version_file);
    }
  }

}
