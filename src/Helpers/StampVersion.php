<?php

namespace AKlump\WebPackage\Helpers;

use AKlump\WebPackage\Config\Config;
use AKlump\WebPackage\Config\ConfigDefaults;
use AKlump\WebPackage\Git\GitProxy;
use AKlump\WebPackage\Model\Version;
use AKlump\WebPackage\Traits\HasConfigTrait;
use AKlump\WebPackage\VersionScribeInterface;
use Symfony\Component\Filesystem\Path;

/**
 * Write the current version, with the build number as its build metadata.
 *
 * An existing build number is replaced, so the result is always "X.Y.Z+N".
 */
class StampVersion {

  use HasConfigTrait;

  /**
   * @var \AKlump\WebPackage\VersionScribeInterface
   */
  private $scribe;

  /**
   * @var \AKlump\WebPackage\Git\GitProxy
   */
  private $git;

  /**
   * @var string
   */
  private $rootPath;

  /**
   * @param array $config
   * @param \AKlump\WebPackage\VersionScribeInterface $scribe
   * @param \AKlump\WebPackage\Git\GitProxy $git
   * @param string $root_path
   *   The absolute path of the project, i.e., the directory containing
   *   .web_package.
   */
  public function __construct($config, VersionScribeInterface $scribe, GitProxy $git, string $root_path) {
    $this->setConfig($config);
    $this->scribe = $scribe;
    $this->git = $git;
    $this->rootPath = $root_path;
  }

  /**
   * @return string
   *   The version that was written.
   *
   * @throws \RuntimeException If the version could not be written.
   */
  public function __invoke(): string {
    $config = $this->getConfig();
    $current = (new GetCurrentVersion($config, $this->scribe))();
    [$core] = explode('+', $current, 2);

    $prefix = Version::parse($core)->getPrefix();
    $build_number = (new GetBuildNumber($this->git, $this->getVersionFileFromGitRoot(), $prefix))();
    $stamped = $core . '+' . $build_number;
    if (!$this->scribe->write($stamped)) {
      throw new \RuntimeException(sprintf('Failed to write the build number to %s', $this->scribe->getFilepath()));
    }

    return $stamped;
  }

  /**
   * @return string
   *   The version file's path relative to the git root, which is how git
   *   reports the files of a commit.
   */
  private function getVersionFileFromGitRoot(): string {
    $config = $this->getConfig();
    $version_file = $config[Config::VERSION_FILE] ?? ConfigDefaults::VERSION_FILE;
    $git_root = Path::join($this->rootPath, $config['git_root'] ?? '.');

    return Path::makeRelative(Path::makeAbsolute($version_file, $this->rootPath), $git_root);
  }

}
