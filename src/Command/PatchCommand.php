<?php

namespace AKlump\WebPackage\Command;

use AKlump\WebPackage\Model\Version;

class PatchCommand extends BaseVersionCommand {

  protected static $defaultName = 'patch';

  protected function configure() {
    $this
      ->setDescription("Increment version one patch step without hooks.");
  }

  protected function getNextVersion(Version $version): Version {
    return $version->getNextPatchVersion();
  }

}
