<?php

namespace AKlump\WebPackage;

use z4kn4fein\SemVer\Version;

interface VersionScribeInterface {

  const DEFAULT = '0.0.0';

  /**
   * Get the version exactly as stored in the version file.
   *
   * @return string
   *   The string verbatim as stored in the file.  To normalize you should use
   *   \z4kn4fein\SemVer\Version::parse($foo->read(), false).
   *
   * @see \z4kn4fein\SemVer\Version
   */
  public function read(): string;

  /**
   * @param \z4kn4fein\SemVer\Version $version
   *
   * @return bool
   *   True if the version was persisted successfully.
   */
  public function write(Version $version): bool;

}
