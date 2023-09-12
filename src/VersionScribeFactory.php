<?php

namespace AKlump\WebPackage;

use AKlump\WebPackage\VersionScribes\DrupalInfo;
use AKlump\WebPackage\VersionScribes\IniFile;
use AKlump\WebPackage\VersionScribes\Json;
use AKlump\WebPackage\VersionScribes\SymfonyConsoleApplication;
use AKlump\WebPackage\VersionScribes\Text;
use AKlump\WebPackage\VersionScribes\Yaml;
use AKlump\WebPackage\Model\Version;

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
//    elseif ('.git' === $basename) {
//      return new GitTags($filepath);
//    }
    elseif (in_array($extension, ['yml', 'yaml'])) {
      return new Yaml($filepath);
    }
    elseif ('php' === $extension) {
      $contents = file_get_contents($filepath);
      if (preg_match("/\->setVersion\(['\"\d\.]+?\)/", $contents)) {
        return new SymfonyConsoleApplication($filepath);
      }
    }
    else {
      // Let's just see if we can find a version in the file contents somewhere.
      // This case will allow us to use a simple text file with the version
      // string in it by itself.
      $contents = file_get_contents($filepath);
      $version = Version::parse($contents, FALSE);
      if (substr_count($contents, (string) $version) === 1) {
        return new Text($filepath);
      }
    }

    return NULL;
  }

}
