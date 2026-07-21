<?php

namespace AKlump\WebPackage\Tests;

use AKlump\WebPackage\Helpers\GetPreviousVersion;
use AKlump\WebPackage\Helpers\Stash;
use AKlump\WebPackage\Model\Context;
use z4kn4fein\SemVer\Version;

trait WriteTestTrait {

  public function filesize(string $path): int {
    return \mb_strlen(file_get_contents($path));
  }

  public function getVersion(): Version {
    return Version::create(rand(0, 3), rand(0, 9), rand(0, 99));
  }


  /**
   * @param \AKlump\WebPackage\Model\Context $context
   *
   * @return string
   *
   * @see \AKlump\WebPackage\Helpers\GetPreviousVersion
   */
  public function setPreviousVersion(Context $context, string $previous_version): void {
    (new Stash($context))->write(GetPreviousVersion::STASH_KEY, $previous_version);
  }

  private function ensureParentDir(string $filepath): void {
    if (!file_exists(dirname($filepath))) {
      mkdir(dirname($filepath), 0755, TRUE);
    }
    if (!is_dir(dirname($filepath))) {
      throw new \RuntimeException(sprintf('Failed to ensure parent directory: %s', $filepath));
    }
  }

  public function getPath(string $extension): string {
    $path = __DIR__ . '/files/temp.' . $extension;
    $this->ensureParentDir($path);

    return $path;
  }

  public function unlink(string $extension): void {
    $path = $this->getPath($extension);
    if (file_exists($path) && !unlink($path)) {
      throw new \RuntimeException(sprintf('Failed to unlink %s', $path));
    }
  }


}
