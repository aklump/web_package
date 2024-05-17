<?php

namespace AKlump\WebPackage\Helpers;

class GetAugmentedFailureMessage {

  public function __invoke(string $message, int $exit_code, string $filepath): string {
    return sprintf("%s\n\n%s -> %d\n", $message, str_replace(getcwd(), '.', $filepath), $exit_code);
  }
}
