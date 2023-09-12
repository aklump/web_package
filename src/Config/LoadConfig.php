<?php

namespace AKlump\WebPackage\Config;

use Symfony\Component\Filesystem\Path;

final class LoadConfig {

  public function __invoke(string $template = NULL): array {
    $base_dir = getcwd();
    $manager = new ConfigManager();

    // The first thing to load is going to be in the home directory, and if we can find a template either in a passed argument on in the project's configuration then we'll use that.  If not we'll use the default config from the home directory.
    if (!$template) {
      $loaded_path = Path::makeAbsolute('.web_package/config', $base_dir);
      $temp = $manager->loadFile($loaded_path);
      if (!empty($temp['template'])) {
        $template = $temp['template'];
      }
    }

    $load_from_home_dir = $manager::getServerHome() . "/.web_package/";
    if ($template) {
      $load_from_home_dir .= "config_$template";
    }
    else {
      $load_from_home_dir .= 'config';
    }
    $config = $manager->loadFile($load_from_home_dir);

    // Next we are going to overwrite values that at at the project level.  The
    // items that come last will overwrite the earlier.
    foreach ([
               $base_dir . '/.web_package/config',
               $base_dir . '/.web_package/local_config',
               $base_dir . '/.web_package/config.local',
             ] as $item) {
      $config = $manager->loadFile($item) + $config;
    }

    $manager->handleGitRoot($config, $base_dir);
    $manager->migrateOldKeys($config);
    $manager->handleInfoFile($config, $base_dir);

    ksort($config);

    return $config;
  }
}
