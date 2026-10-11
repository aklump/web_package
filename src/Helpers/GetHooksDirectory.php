<?php

namespace AKlump\WebPackage\Helpers;

use AKlump\WebPackage\Config\ConfigDefaults;

class GetHooksDirectory {

  /**
   * @var string
   */
  private $rootPath;

  /**
   * @param string $root_path
   *   The absolute path of the project, i.e., the directory containing
   *   .web_package.
   */
  public function __construct(string $root_path) {
    $this->rootPath = $root_path;
  }

  /**
   * @param string $hook_type
   *
   * @return string
   */
  public function __invoke(string $hook_type): string {
    if (empty($hook_type)) {
      throw new \InvalidArgumentException('$hook_type may not be empty');
    }

    return $this->rootPath . '/' . ConfigDefaults::INSTALL_DIR . "/hooks/$hook_type/";
  }

}
