<?php

namespace AKlump\WebPackage\Config;

use AKlump\WebPackage\Config\Loader\LegacyLoader;
use AKlump\WebPackage\Config\Loader\YamlLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Filesystem\Filesystem;
use Exception;
use Symfony\Component\Filesystem\Path;

class ConfigManager {

  const DEFAULT_VERSION_FILENAME = '.version';

  static function getServerHome(): ?string {
    $home = getenv('HOME');
    if (!empty($home)) {
      // home should never end with a trailing slash.
      $home = rtrim($home, '/');
    }
    elseif (!empty($_SERVER['HOMEDRIVE']) && !empty($_SERVER['HOMEPATH'])) {
      // home on windows
      $home = $_SERVER['HOMEDRIVE'] . $_SERVER['HOMEPATH'];
      // If HOMEPATH is a root directory the path can end with a slash. Make sure
      // that doesn't happen.
      $home = rtrim($home, '\\/');
    }

    return empty($home) ? NULL : $home;
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
    $filesystem = new Filesystem();
    if (!$filesystem->isAbsolutePath($path_to_load)) {
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

  public function handleInfoFile(array &$config, string $base_dir): void {
    if (!empty($config['info_file'])
      && strstr($config['info_file'], '*')) {
      $config['info_file'] = glob($base_dir . '/' . $config['info_file'])[0] ?? $config['info_file'];
    }
    if (empty($config['info_file'])) {
      $config['info_file'] = self::DEFAULT_VERSION_FILENAME;
    }
  }

}
