<?php

namespace AKlump\WebPackage;

use AKlump\WebPackage\VersionScribes\DrupalInfo;
use AKlump\WebPackage\VersionScribes\IniFile;
use AKlump\WebPackage\VersionScribes\Json;
use AKlump\WebPackage\VersionScribes\SymfonyConsoleApplication;
use AKlump\WebPackage\VersionScribes\Yaml;

class VersionScribeFactory {

  /**
   * @param string $filepath
   *   The file should already exist.  Detection is based on a combination of
   *   file contents and naming patterns.
   *
   * @return \AKlump\WebPackage\VersionScribeInterface|null
   *
   * @throws \InvalidArgumentException If the file does not exist.
   */
  public function __invoke(string $filepath): ?VersionScribeInterface {
    if (!file_exists($filepath)) {
      throw new \InvalidArgumentException(sprintf('The path does not exist: %s', $filepath));
    }
    $basename = strtolower(basename($filepath));
    $extension = pathinfo($basename, PATHINFO_EXTENSION);
    if ('json' === $extension) {
      return new Json($filepath);
    }
    elseif ('info' === $extension) {
      return new DrupalInfo($filepath);
    }
    elseif ('ini' === $extension) {
      return new IniFile($filepath);
    }
    elseif (in_array($extension, ['yml', 'yaml'])) {
      return new Yaml($filepath);
    }
    elseif ('php' === $extension) {
      $contents = file_get_contents($filepath);
      if (preg_match("/\->setVersion\(['\"\d\.]+?\)/", $contents)) {
        return new SymfonyConsoleApplication($filepath);
      }
    }

    return NULL;
  }

}
