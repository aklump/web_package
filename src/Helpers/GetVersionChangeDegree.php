<?php

namespace AKlump\WebPackage\Helpers;

use AKlump\WebPackage\Model\Version;

class GetVersionChangeDegree {

  /**
   * @param string $a
   * @param string $b
   *
   * @return string|null
   *   Null if the versions are the same.
   */
  public function __invoke($a, $b): ?string {
    $a = Version::parse($a);
    $b = Version::parse($b);

    if ((string) $a->getMajor() !== (string) $b->getMajor()) {
      return VersionDegree::MAJOR;
    }
    if ((string) $a->getMinor() !== (string) $b->getMinor()) {
      return VersionDegree::MINOR;
    }
    if ((string) $a->getPatch() !== (string) $b->getPatch()) {
      return VersionDegree::PATCH;
    }

    return NULL;
  }

}
