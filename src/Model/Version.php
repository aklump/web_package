<?php

namespace AKlump\WebPackage\Model;

use AKlump\WebPackage\VersionScribeInterface;

/**
 * A wrapper class to handle more variations on version strings.
 *
 * @see \z4kn4fein\SemVer\Version
 *
 * @method getNextPatchVersion
 * @method getNextMinorVersion
 * @method getNextMajorVersion
 * @method getMajor
 * @method getMinor
 * @method getPatch
 */
class Version {

  private $prefix = '';

  private $suffix = '';

  private $semver;

  public function __construct(string $version) {
    try {
      $this->semver = \z4kn4fein\SemVer\Version::parse($version, FALSE);
    }
    catch (\Exception $exception) {
      list($this->prefix, $this->semver, $this->suffix) = $this->parseOddballs($version);
    }
  }

  private function parseOddballs($string): array {
    $prefix = '';
    $semver = \z4kn4fein\SemVer\Version::parse(VersionScribeInterface::DEFAULT);
    $suffix = '';

    do {
      $prefix .= substr($string, 0, 1);
      $string = substr($string, 1);
      try {
        $semver = \z4kn4fein\SemVer\Version::parse($string, FALSE);
        break;
      }
      catch (\Exception $exception) {
        continue;
      }
    } while ($string);

    return [$prefix, $semver, $suffix];
  }


  public static function parse(string $version) {
    return new static($version);
  }

  public function __toString(): string {
    return $this->prefix . (string) $this->semver . $this->suffix;
  }

  public function __call($method, $args) {
    $result = clone $this;
    $result->semver = call_user_func_array([$result->semver, $method], $args);

    return $result;
  }

}
