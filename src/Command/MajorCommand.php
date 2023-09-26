<?php

namespace AKlump\WebPackage\Command;

use AKlump\WebPackage\Model\Version;

class MajorCommand extends BaseVersionCommand {

  protected static $defaultName = 'major';

  protected function configure() {
    $this
      ->setDescription("Increment version one major step without hooks.");
  }

  protected function getNextVersion(Version $version): Version {
    return $version->getNextMajorVersion();
  }

}
