<?php

namespace AKlump\WebPackage\Helpers;

use AKlump\WebPackage\Git\GitProxy;

/**
 * Get the build number: the commits made since the newest release tag.
 *
 * Commits at the tip of the branch that changed nothing but the version file
 * are not counted.  Those are the commits that record a previous build number,
 * and counting them would raise the number every time it was stamped.
 */
class GetBuildNumber {

  /**
   * @var \AKlump\WebPackage\Git\GitProxy
   */
  private $git;

  /**
   * @var string
   */
  private $versionFile;

  /**
   * @var string
   */
  private $prefix;

  /**
   * @param \AKlump\WebPackage\Git\GitProxy $git
   * @param string $version_file
   *   The path to the version file, relative to the git root.
   * @param string $prefix
   *   The prefix of the project's versions, e.g. "8.x-"; only tags with this
   *   prefix are considered release tags.
   */
  public function __construct(GitProxy $git, string $version_file, string $prefix = '') {
    $this->git = $git;
    $this->versionFile = $version_file;
    $this->prefix = $prefix;
  }

  public function __invoke(): int {
    $previous_tag = $this->getNewestReleaseTag();
    $tip = $this->skipVersionOnlyCommits('HEAD');

    return $this->git->countCommits($previous_tag ? "$previous_tag..$tip" : $tip);
  }

  /**
   * @return string
   *   The newest tag that looks like a version, or an empty string.  A leading
   *   "v" is allowed, as is a missing minor or patch number.
   */
  private function getNewestReleaseTag(): string {
    $pattern = '/^' . preg_quote($this->prefix, '/') . '\d+(\.\d+){0,2}$/';
    foreach ($this->git->getTagsByCreationDate() as $tag) {
      $name = 0 === strpos($tag, 'v') ? substr($tag, 1) : $tag;
      if (preg_match($pattern, $name)) {
        return $tag;
      }
    }

    return '';
  }

  /**
   * @param string $ref
   *
   * @return string
   *   The first commit, walking back from $ref, that changed more than the
   *   version file.  If every commit changed only the version file, this is the
   *   root commit.
   */
  private function skipVersionOnlyCommits(string $ref): string {
    while ([$this->versionFile] === $this->git->getFilesInCommit($ref)) {
      $parent = $this->git->getParent($ref);
      if ('' === $parent) {
        break;
      }
      $ref = $parent;
    }

    return $ref;
  }

}
