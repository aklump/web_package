<?php

namespace AKlump\WebPackage\Tests;

use z4kn4fein\SemVer\Version;

trait WriteTestTrait {

  public function filesize(string $path): int {
    return \mb_strlen(file_get_contents($path));
  }

  public function getVersion(): Version {
    return Version::create(rand(0, 3), rand(0, 9), rand(0, 99));
  }

  public function getPath(string $extension): string {
    return __DIR__ . '/files/temp.' . $extension;
  }

  public function unlink(string $extension): void {
    $path = $this->getPath($extension);
    if (file_exists($path) && !unlink($path)) {
      throw new \RuntimeException(sprintf('Failed to unlink %s', $path));
    }
  }


}
