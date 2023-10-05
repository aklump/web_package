<?php

namespace AKlump\WebPackage;

use AKlump\WebPackage\Model\Version;

interface VersionScribeInterface {

  /**
   * @var string The default filename (no extension) for created files indicated
   * with a glob char.
   *
   */
  const DEFAULT_FILENAME = 'web_package';

  /**
   * Get the version EXACTLY as stored in the version file.
   *
   * @return string
   *   The version, verbatim as stored in the file.
   *
   * @see \AKlump\WebPackage\Model\Version::parse()
   *
   * @see \AKlump\WebPackage\Config\ConfigDefaults::INITIAL_VERSION
   */
  public function read(): string;

  /**
   * @param \z4kn4fein\SemVer\Version $version
   *
   * @return bool
   *   True if the version was persisted successfully.
   */
  public function write(string $version): bool;

  public function getFilepath(): string;

}
