<?php

namespace AKlump\WebPackage\Traits;

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
  private function replaceVersionInFile(string $filepath, string $old, string $new): bool {
    $new = $this->rtrimZerosToMatchFormat($old, $new);
    $contents = file_get_contents($filepath);
    $count = substr_count($contents, (string) $old);
    if ($count === 1) {
      $contents = str_replace((string) $old, (string) $new, $contents);

      return file_put_contents($filepath, $contents);
    }

    return FALSE;
  }

  /**
   * Replace the version string in a file using a regex pattern.
   *
   * @param string $filepath
   * @param string $regex
   *   The regex pattern must capture the prefix and suffix as [1] and [3];
   *   whatever these are, they will wrap $new in the replacement. Here is an
   *   example: /(version=")([\d\.]+)(")/i
   * @param string $old
   *   The old version, this will be used to match formatting.
   * @param string $new
   *   The new version to insert.
   *
   * @return bool
   *   True if the save was successful.
   */
  private function regexReplaceVersionInFile(string $filepath, string $regex, string $old, string $new): bool {
    if (!file_exists($this->source)) {
      return FALSE;
    }
    $new = $this->rtrimZerosToMatchFormat($old, $new);
    $contents = file_get_contents($filepath);
    $update = preg_replace_callback($regex, function (array $matches) use ($new) {
      return $matches[1] . $new . $matches[3];
    }, $contents);
    if ($update === $contents) {
      return FALSE;
    }

    return file_put_contents($filepath, $update);
  }

  /**
   * Strip zeros from right to match the format of $modal_version.
   *
   * @param string $model_version
   * @param string $version
   *
   * @return string
   *   $version with all right-hand zeros removed so that it matches the same
   *   specificity of $model_version, e.g. Given $model_version = '9' then
   *   $version = '10.0.0' -> $version = '10'; given $model_version = '9.3' then
   *   $version = '10.1.0' -> $version = '10.1';
   */
  private function rtrimZerosToMatchFormat(string $model_version, string $version) {
    $model_count = count(explode('.', $model_version));
    $version_parts = explode('.', $version);

    while (end($version_parts) == 0 && count($version_parts) > $model_count) {
      array_pop($version_parts);
    }

    return implode('.', $version_parts);
  }

}
