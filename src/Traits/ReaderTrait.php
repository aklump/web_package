<?php

namespace AKlump\WebPackage\Traits;


use z4kn4fein\SemVer\SemverException;
use z4kn4fein\SemVer\Version;

trait ReaderTrait {

  public function getVersion(string $raw): Version {
    try {
      if (!empty($raw)) {
        return Version::parse($raw, FALSE);
      }
    }
    catch (SemverException $e) {
      // Purposefully left blank.
    }

    return Version::parse(\AKlump\WebPackage\VersionScribeInterface::DEFAULT, FALSE);
  }

}
