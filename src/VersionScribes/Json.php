<?php

namespace AKlump\WebPackage\VersionScribes;

use AKlump\WebPackage\Traits\WriterTrait;
use AKlump\WebPackage\VersionScribeInterface;
use z4kn4fein\SemVer\Version;

class Json implements VersionScribeInterface {

  use WriterTrait;

  private $source;

  public function __construct(string $source) {
    $this->source = $source;
  }

  public function read(): string {
    if (file_exists($this->source)) {
      $json = file_get_contents($this->source);
      $data = json_decode($json, TRUE);
      $data = array_change_key_case($data);
    }

    return $data['version'] ?? VersionScribeInterface::DEFAULT;
  }

  /**
   * @inheritDoc
   */
  public function write(Version $version): bool {
    if (file_exists($this->source)) {
      $old = $this->read();
      if ($this->replaceVersionInFile($this->source, $old, $version)) {
        return TRUE;
      }
      // TODO Handle what to do here.
      throw new \RuntimeException(sprintf('The version %s does not appear in %s exactly once; update failed.', (string) $old, $this->source));
    }
    $data = json_encode(['version' => (string) $version], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

    return file_put_contents($this->source, $data);
  }
}
