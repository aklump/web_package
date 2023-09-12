<?php

namespace AKlump\WebPackage\Model;

use AKlump\WebPackage\Helpers\NormalizeFeatureBranch;
use AKlump\WebPackage\Traits\ShellCommandTrait;

/**
 * @url https://nvie.com/posts/a-successful-git-branching-model/
 */
class GitFlow {

  use ShellCommandTrait;

  const MASTER = 'master';

  const DEVELOP = 'develop';

  const FEATURE = 'feature';

  const RELEASE = 'release';

  const HOTFIX = 'hotfix';

  private $type;

  private $master;

  private $develop;

  /**
   * Create a new instance representing a GitFlow model for a $branch_type.
   *
   * @param string $branch_type
   *   The branch type that this instance will represent.
   * @param $master_branch
   *   The name of the master branch.
   * @param $develop_branch
   *   The name of the develop branch.  This can be the same as $master_branch,
   *   which effectively changes the GitFlow model to something more simple.
   */
  public function __construct(string $branch_type, $master_branch = self::MASTER, $develop_branch = self::DEVELOP) {
    if (in_array($branch_type, [
      self::FEATURE,
      self::RELEASE,
      self::HOTFIX,
    ])) {
      $this->type = $branch_type;
    }
    $this->master = $master_branch;
    $this->develop = $develop_branch;
  }

  public function getMayBranchOffFrom(): array {
    if ($this->type === self::HOTFIX) {
      return [$this->master];
    }
    elseif (in_array($this->type, [self::RELEASE, self::FEATURE])) {
      return [$this->develop];
    }

    return [];
  }

  public function getMustMergeBackInto(): array {
    if ($this->type === self::FEATURE) {
      return [$this->develop];
    }
    elseif (in_array($this->type, [self::RELEASE, self::HOTFIX])) {
      // Order is important here, as it determines the merge order.
      return array_unique([$this->develop, $this->master]);
    }

    return [];
  }

  public function getMayFinish(): array {
    return [GitFlow::FEATURE, GitFlow::RELEASE, GitFlow::HOTFIX];
  }

  /**
   * @param string $base
   * @param string $type
   *
   * @return string
   *
   * @throws \InvalidArgumentException
   *   If $type is unexpected.
   */
  public function getBranchName(string $base): string {
    if (self::FEATURE === $this->type) {
      try {
        $author = $this->exec('git log -1 --pretty=format:"%an"');
      }
      catch (\Exception $exception) {
        $author = '';
      }

      return (new NormalizeFeatureBranch())($base, $author);
    }
    elseif (self::RELEASE === $this->type) {
      return 'release-' . $base;
    }
    elseif (self::HOTFIX === $this->type) {
      return 'hotfix-' . $base;
    }

    return $base;
  }

}
