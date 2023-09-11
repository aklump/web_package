<?php

namespace AKlump\WebPackage\VersionScribes;

use AKlump\WebPackage\Traits\WriterTrait;
use AKlump\WebPackage\VersionScribeInterface;
use z4kn4fein\SemVer\Version;

class DrupalInfo implements VersionScribeInterface {

  use WriterTrait;

  const REGEX = '/^(version\s*=.*?)([\d\.]+)(.*?' . PHP_EOL . ')/m';

  private $source;

  public function __construct(string $source) {
    $this->source = $source;
  }

  public function read(): string {
    if (file_exists($this->source)) {
      $contents = file_get_contents($this->source);
      preg_match(self::REGEX, $contents, $matches);
    }

    return $matches[2] ?? VersionScribeInterface::DEFAULT;
  }

  /**
   * {@inheritdoc}
   */
  public function write(Version $version): bool {
    if (file_exists($this->source)) {
      return $this->regexReplaceVersionInFile($this->source, self::REGEX, $this->read(), $version);
    }

    return file_put_contents($this->source, 'version = "' . $version . '"' . PHP_EOL);
  }

}
