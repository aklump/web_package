<?php

namespace AKlump\WebPackage\Helpers;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

class GetRootPath {

  /**
   * Return the root path that contains .web_package.
   *
   * @param string $start_dir
   *   This is probably going to be getcwd().
   *
   * @return string
   *   Beginning with $start_dir, we look for .web_package, if not found, we go
   *   up a level until we find it.  If we get to / and haven't found it, then
   *   we are outside of an initialized project.  In that case $start_dir is
   *   returned.  Also of note, the home directory will never be returned even
   *   though it may container .web_package; it is not technically an
   *   initialized project.
   */
  public function __invoke(string $start_dir): string {
    $path = $start_dir;
    $home = (new GetServerHome())();
    $filesystem = new Filesystem();
    while ($path && $path !== '/' && (!$filesystem->exists("$path/.web_package") || $path === $home)) {
      $path = Path::normalize(dirname($path));
    }

    $path = rtrim($path, '/');
    if (!$path) {
      return $start_dir;
    }

    return $path !== $home ? $path : $start_dir;
  }

}
