<?php

namespace AKlump\WebPackage\Git;

use AKlump\WebPackage\Traits\ShellCommandTrait;

/**
 * Wrapper around CLI git to facilitate unit testing.
 *
 * Every argument is passed through escapeshellarg().
 */
class GitProxy {

  use ShellCommandTrait;

  public function tag(string $tag_name) {
    return $this->system(sprintf('git tag %s', escapeshellarg($tag_name)));
  }

  public function deleteBranch(string $branch_name) {
    return $this->system(sprintf('git branch -d %s', escapeshellarg($branch_name)));
  }

  public function createBranch(string $new_branch, string $original_branch) {
    return $this->system(sprintf('git checkout -b %s %s', escapeshellarg($new_branch), escapeshellarg($original_branch)));
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
   */
  public function mergeBranch(string $merge_into_branch, string $branch_to_merge): void {
    $this->system(sprintf('git checkout %s', escapeshellarg($merge_into_branch)));
    $this->system(sprintf(
      'git merge --no-ff %s -m %s',
      escapeshellarg($branch_to_merge),
      escapeshellarg('Merge branch ' . $branch_to_merge)
    ));
  }

  public function commitFile(string $file, string $commit_message) {
    $this->system(sprintf('git commit %s -m %s', escapeshellarg($file), escapeshellarg($commit_message)));
  }

  /**
   * @url https://git-scm.com/docs/git-push#_examples
   */
  public function push(string $ref) {
    // TODO Do we need "origin" to be configurable?
    return $this->system(sprintf('git push origin %s', escapeshellarg($ref)));
  }

  /**
   * Get the uncommitted changes in the working tree.
   *
   * @return string[]
   *   One entry per line of `git status --short`.  An empty array when the tree
   *   is clean or when the current directory is not a git repository.
   */
  public function getUncommittedFiles(): array {
    $lines = [];
    try {
      $this->exec('git status --short', $lines);
    }
    catch (\RuntimeException $exception) {
      // If we are not in a git repo, we don't care about uncommitted changes.
      return [];
    }

    return $lines;
  }

}
