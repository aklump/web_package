<?php

namespace AKlump\WebPackage\VersionScribes;

class MissingFileScribe implements \AKlump\WebPackage\VersionScribeInterface {

  /**
   * @inheritDoc
   */
  public function read(): string {
    return '';
  }

  /**
   * @inheritDoc
   */
  public function write(string $version): bool {
    return FALSE;
  }

  public function getFilepath(): string {
    return '';
    //    if (!isset($this->filepath)) {
    //      $this->filepath = tempnam(sys_get_temp_dir(), 'web_package');
    //    }
    //
    //    return $this->filepath;
  }
}
