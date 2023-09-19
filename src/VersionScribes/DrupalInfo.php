<?php

namespace AKlump\WebPackage\VersionScribes;

use AKlump\WebPackage\Traits\WriterTrait;
use AKlump\WebPackage\VersionScribeInterface;
use AKlump\WebPackage\Model\Version;

class DrupalInfo implements VersionScribeInterface {

  use WriterTrait;

  const REGEX = '/^(version\s*=.*?)([\d\.\-xrc]+)(.*?\n)/m';

  private $source;

  public function __construct(string $source) {
    $this->source = $source;
  }

  public function getFilepath(): string {
    return $this->source;
  }

  public function read(): ?string {
    if (file_exists($this->source)) {
      $contents = file_get_contents($this->source);
      preg_match(self::REGEX, $contents, $matches);
    }
    if (empty($matches[2])) {
      return NULL;
    }

    return $matches[2];
  }


  /**
   * {@inheritdoc}
   */
  public function write(string $version): bool {
    if (file_exists($this->source)) {
      $status = $this->regexReplaceVersionInFile($this->source, self::REGEX, $this->read(), $version);
    }
    if (empty($status)) {
      $status = (bool) file_put_contents($this->source, 'version = "' . $version . '"' . PHP_EOL);
    }

    return $status;
  }

}

