<?php

namespace AKlump\WebPackage;

use z4kn4fein\SemVer\Version;

interface VersionScribeInterface {

  const DEFAULT = '0.0.0';

  public function read(): Version;

  /**
   * @param \z4kn4fein\SemVer\Version $version
   *
   * @return bool
   *   True if the version was persisted successfully.
   */
  public function write(Version $version): bool;

}
