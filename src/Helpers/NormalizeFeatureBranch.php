<?php

namespace AKlump\WebPackage\Helpers;

use AKlump\WebPackage\Traits\ShellCommandTrait;
use Jawira\CaseConverter\Convert;

/**
 * Normalize a Git feature branch name.
 *
 * @url https://codingsight.com/git-branching-naming-convention-best-practices/
 */
class NormalizeFeatureBranch {

  const GLUE = '_';

  use ShellCommandTrait;

  public function __invoke(string $name, string $author): string {
    $parts = array_filter([$author, 'feature', $name]);
    $parts = array_map(function (string $part) {
      return (new Convert($part))->toKebab();
    }, $parts);

    return implode(self::GLUE, $parts);
  }

}
