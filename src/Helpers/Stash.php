<?php

namespace AKlump\WebPackage\Helpers;

use AKlump\WebPackage\Model\Context;
use AKlump\WebPackage\Traits\HasContextTrait;

/**
 * @covers \AKlump\WebPackage\Helpers\Stash
 */
class Stash {

  use HasContextTrait;

  public function __construct(Context $context) {
    $this->setContext($context);
  }

  public function read(string $key): string {
    $path = $this->getFilePath($key);
    if (!file_exists($path)) {
      return '';
    }

    return file_get_contents($path);
  }

  public function write(string $key, string $value): void {
    file_put_contents($this->getFilePath($key), $value);
  }

  private function getFilePath($key): string {
    $path = $this->getContext()
        ->getRootPath() . "/.web_package/.stash.$key.txt";
    if (!file_exists(dirname($path))) {
      mkdir(dirname($path), 0755, TRUE);
    }

    return $path;
  }

}
