<?php

namespace AKlump\WebPackage\Access;

use AKlump\WebPackage\Helpers\GetRootPath;
use Symfony\Component\Filesystem\Filesystem;

final class IsInitialized implements AccessInterface {

  /**
   * @return bool
   *   True if the directory is already initialized.
   */
  public function access(): bool {
    $root_path = (new GetRootPath())() ?? getcwd();

    return (new Filesystem())->exists($root_path . '/.web_package');
  }
}
