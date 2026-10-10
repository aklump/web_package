<?php

namespace AKlump\WebPackage\Helpers;

class GetCurrentBranch {

  public function __invoke(): string {
    $branch = shell_exec('git rev-parse --abbrev-ref HEAD 2>/dev/null');

    // shell_exec() returns NULL when the command fails, e.g. outside a git repo.
    return trim((string) $branch);
  }

}
