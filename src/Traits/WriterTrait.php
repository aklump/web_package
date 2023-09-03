<?php

namespace AKlump\WebPackage\Traits;


use z4kn4fein\SemVer\Version;

trait WriterTrait {

  /**
   * @param string $filepath
   * @param \z4kn4fein\SemVer\Version $old
   * @param \z4kn4fein\SemVer\Version $new
   *
   * @return bool
   *   This will be false if a simple find/replace could not be performed; in
   *   such case you will need to take file-type-specific steps to update the
   *   file version string.
   */
  public function replaceVersionInFile(string $filepath, Version $old, Version $new): bool {
    $contents = file_get_contents($filepath);
    $count = substr_count($contents, (string) $old);
    if ($count === 1) {
      $contents = str_replace((string) $old, (string) $new, $contents);

      return file_put_contents($filepath, $contents);
    }

    return FALSE;
  }

}
