<?php

namespace AKlump\WebPackage\Helpers;

use AKlump\WebPackage\Config\Config;
use AKlump\WebPackage\Config\ConfigDefaults;
use AKlump\WebPackage\Hooks\HookEvent;
use AKlump\WebPackage\Traits\HasConfigTrait;
use Symfony\Component\Filesystem\Path;

class GetHookEvent {

  use HasConfigTrait;

  /**
   * @var string
   */
  private $rootPath;

  /**
   * @param array $config
   * @param string $root_path
   *   The absolute path of the project, i.e., the directory containing
   *   .web_package.
   */
  public function __construct($config, string $root_path) {
    $this->setConfig($config);
    $this->rootPath = $root_path;
  }

  /**
   * @return \AKlump\WebPackage\Hooks\HookEvent
   *   A hook event with known info already set.
   */
  public function __invoke(): HookEvent {
    $config = $this->getConfig();
    $event = new HookEvent();
    $info = (new GetInfo($config, $this->rootPath))();
    $event->setPackageName($info['name'] ?? '');
    $event->setDescription($info['description'] ?? '');
    $event->setAuthor($info['author'] ?? '');
    $event->setHomepage($info['homepage'] ?? '');
    $event->setRoot($this->rootPath);

    $info_file = $config[Config::VERSION_FILE] ?? ConfigDefaults::VERSION_FILE;
    if (!Path::isAbsolute($info_file)) {
      $info_file = Path::makeAbsolute($info_file, $this->rootPath);
    }
    $event->setInfoFile($info_file);

    return $event;
  }

}
