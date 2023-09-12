<?php

namespace AKlump\WebPackage\Config;

use AKlump\WebPackage\Helpers\GetRootPath;
use AKlump\WebPackage\Helpers\GetServerHome;
use Symfony\Component\Filesystem\Path;


/**
 * // TODO This was based on the legacy version and needs to be simplified.
 */
final class LoadConfig {

  static private $defaults = [
    'master' => 'main',
    'develop' => 'main',
  ];

  public function __invoke(string $template = NULL): array {
    $root_path = (new GetRootPath())() ?? getcwd();
    $manager = new ConfigManager();

    // The first thing to load is going to be in the home directory, and if we can find a template either in a passed argument on in the project's configuration then we'll use that.  If not we'll use the default config from the home directory.
    if (!$template) {
      $loaded_path = Path::makeAbsolute('.web_package/config', $root_path);
      $temp = $manager->loadFile($loaded_path);
      if (!empty($temp['template'])) {
        $template = $temp['template'];
      }
    }

    $load_from_home_dir = (new GetServerHome())() . "/.web_package/";
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
               $root_path . '/.web_package/config',
               $root_path . '/.web_package/local_config',
               $root_path . '/.web_package/config.local',
             ] as $item) {
      $config = $manager->loadFile($item) + $config;
    }

    $manager->handleGitRoot($config, $root_path);
    $manager->migrateOldKeys($config);
    $manager->handleInfoFile($config, $root_path);

    ksort($config);

    // TODO Return an object instead of array?
    return $config + self::$defaults;
  }

}
