<?php

namespace AKlump\WebPackage\Git;

use AKlump\WebPackage\Traits\ShellCommandTrait;

class GitProxy {

  use ShellCommandTrait;

  public function tag(string $tag_name) {
    return $this->system(sprintf('git tag %s', $tag_name));
  }

  public function deleteBranch(string $branch_name) {
    return $this->system(sprintf('git branch -d %s', $branch_name));
  }

  /**
   * @url https://git-scm.com/docs/git-push#_examples
   */
  public function push(string $ref) {
    // TODO Do we need "origin" to be configurable?
    return $this->system(sprintf('git push %s %s', 'origin', $ref));
  }

}
