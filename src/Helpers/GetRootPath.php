<?php

namespace AKlump\WebPackage\Helpers;

use Symfony\Component\Filesystem\Filesystem;

class GetRootPath {

  /**
   * Return the root path that contains .web_package based on getcwd().
   *
   * @return string|null
   *   This will return null if outside of project.  Also the home directory
   *   cannot be used as it contains the global .web_package folder; so that
   *   will be a NULL as well.
   */
  public function __invoke(): ?string {
    $path = getcwd();
    $home = (new GetServerHome())();
    $filesystem = new Filesystem();
    while ($path && $path !== '/' && (!$filesystem->exists("$path/.web_package") || $path === $home)) {
      $path = rtrim(dirname($path), '/\/');
    }

    if (!$path) {
      return NULL;
    }

    return $path !== $home ? $path : NULL;
  }

}
