<?php

namespace AKlump\WebPackage\Helpers;

class CombineBranchNameParts {

  public function __invoke(array $nameParts): string {
    return implode('--', $nameParts);
  }
}
