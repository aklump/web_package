<?php

namespace AKlump\WebPackage\Command;

use AKlump\WebPackage\Model\Version;

class MinorCommand extends BaseVersionCommand {

  protected static $defaultName = 'minor';

  protected function configure() {
    $this
      ->setDescription("Increment version one minor step without hooks.");
  }

  protected function getNextVersion(Version $version): Version {
    return $version->getNextMinorVersion();
  }

}
