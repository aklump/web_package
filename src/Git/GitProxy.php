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

  public function createBranch(string $new_branch, string $original_branch) {
    return $this->system(sprintf('git checkout -b %s %s', $new_branch, $original_branch));
  }

  /**
   * Merge one branch into another.
   *
   * There is no need to call \AKlump\WebPackage\Git\GitProxy::createBranch()
   * first, as this method handles that aspect of the procedure.
   *
   * @param string $merge_into_branch
   *   The branch into which $branch_to_merge will be merged.
   * @param string $branch_to_merge
   *
   * @return string
   */
  public function mergeBranch(string $merge_into_branch, string $branch_to_merge): void {
    $this->system(sprintf('git checkout %s', $merge_into_branch));
    $this->system(sprintf('git merge --no-ff %s -m "Merge branch %s"', $branch_to_merge, $branch_to_merge));
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
