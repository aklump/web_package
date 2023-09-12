<?php

namespace AKlump\WebPackage\Helpers;

class GetAllBranches {

  use \AKlump\WebPackage\Traits\ShellCommandTrait;

  public function __invoke(): array {
    $output = [];
    try {
      $this->exec("git for-each-ref --format='%(refname)' refs/heads/", $output);
    }
    catch (\Exception $exception) {
      return [];
    }

    return array_map(function ($line) {
      return substr($line, 11);
    }, $output);
  }

}
