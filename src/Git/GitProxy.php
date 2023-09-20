<?php

namespace AKlump\WebPackage\Git;

use AKlump\WebPackage\Traits\ShellCommandTrait;

/**
 * Wrapper around CLI git to facilitate unit testing.
 */
class GitProxy {

  use ShellCommandTrait;

  public function tag(string $tag_name) {
    return $this->system(sprintf('git tag %s', $tag_name));
  }

  public function deleteBranch(string $branch_name) {
    return $this->system(sprintf('git branch -d %s', $branch_name));
  }

  public function checkoutBranch(string $new_branch, string $original_branch) {
    return $this->system(sprintf('git checkout -b %s %s', $new_branch, $original_branch));
  }

  public function commitFile(string $file, string $commit_message) {
    $this->system(sprintf('git commit %s -m "%s"', $file, $commit_message));
  }

  /**
   * @url https://git-scm.com/docs/git-push#_examples
   */
  public function push(string $ref) {
    // TODO Do we need "origin" to be configurable?
    return $this->system(sprintf('git push %s %s', 'origin', $ref));
  }

}
