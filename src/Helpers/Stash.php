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
    $path = $this->createPath($key);
    if (!file_exists($path)) {
      return '';
    }

    return file_get_contents($path);
  }

  public function write(string $key, string $value): void {
    file_put_contents($this->createPath($key), $value);
  }

  private function createPath(string $key): string {
    $path = self::getStashPath($this->getContext(), $key);
    if (!file_exists(dirname($path))) {
      mkdir(dirname($path), 0755, TRUE);
    }

    return $path;
  }

  /**
   * Get the absolute filepath for a stashed value.
   *
   * @param Context $context
   * @param $stash_key
   *
   * @return string
   */
  private static function getStashPath(Context $context, $stash_key): string {
    return $context->getRootPath() . "/.web_package/.stash.$stash_key.txt";
  }

}
