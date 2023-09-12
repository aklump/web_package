<?php

namespace AKlump\WebPackage\Access;

use Symfony\Component\Filesystem\Filesystem;

class IsInitialized implements AccessInterface {

  public function __construct(string $directory) {
    $this->dir = $directory;
  }

  /**
   * @return bool
   *   True if the directory is already initialized.
   */
  public function access(): bool {
    return (new Filesystem())->exists($this->dir . '/.web_package');
  }
}
