<?php

namespace AKlump\WebPackage\Access;

use AKlump\WebPackage\Config\ConfigDefaults;
use Symfony\Component\Filesystem\Filesystem;

final class IsInitialized implements AccessInterface {

  /**
   * @var string
   */
  private $rootPath;

  /**
   * @param string $root_path
   *   The absolute path of the project to check.
   */
  public function __construct(string $root_path) {
    $this->rootPath = $root_path;
  }

  /**
   * @return bool
   *   True if the directory is already initialized.
   */
  public function access(): bool {
    return (new Filesystem())->exists($this->rootPath . '/' . ConfigDefaults::INSTALL_DIR);
  }
}
