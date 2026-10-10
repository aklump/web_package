<?php

namespace AKlump\WebPackage\Tests\TestingTraits;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

/**
 * Create a throw-away bump project, with a throw-away home directory, on disk.
 *
 * Call removeTempProject() at the end of the test (or from tearDown) to delete
 * the directories and to restore the HOME environment variable.
 *
 * The scratch directory lives in the gitignored TestingTraits/files/.
 */
trait TempProjectTrait {

  /**
   * @var string|null
   */
  private $tempProjectScratchDir;

  /**
   * @var string|false|null
   *   The HOME value to restore, NULL if HOME has not been replaced.
   */
  private $tempProjectOriginalHome;

  /**
   * @param array $config
   *   Written to .web_package/config.yml.  Pass an empty array and no
   *   .web_package will be created unless $files creates it.
   * @param array $files
   *   Keys are paths relative to the project root, values are the contents.
   *   Parent directories are created.
   * @param array $home_files
   *   Keys are paths relative to the (fake) HOME directory, values are the
   *   contents.  HOME is always pointed at an empty directory so that tests
   *   never read the developer's real ~/.web_package.
   *
   * @return string
   *   The absolute path to the project root.
   */
  public function createTempProject(array $config = [], array $files = [], array $home_files = []): string {
    if ($this->tempProjectScratchDir) {
      throw new \RuntimeException('Call removeTempProject() before creating another temp project.');
    }
    $this->tempProjectScratchDir = __DIR__ . '/files/project_' . uniqid('', TRUE);
    $project = $this->tempProjectScratchDir . '/Temp_Project';
    $home = $this->tempProjectScratchDir . '/home';
    $filesystem = new Filesystem();
    $filesystem->mkdir([$project, $home]);

    $this->tempProjectOriginalHome = getenv('HOME');
    putenv('HOME=' . realpath($home));

    if ($config) {
      $files['.web_package/config.yml'] = Yaml::dump($config);
    }
    foreach ($files as $path => $contents) {
      $filesystem->dumpFile($project . '/' . $path, $contents);
    }
    foreach ($home_files as $path => $contents) {
      $filesystem->dumpFile($home . '/' . $path, $contents);
    }

    return realpath($project);
  }

  /**
   * @return string
   *   The absolute path to the fake HOME directory of the temp project.
   */
  public function getTempProjectHome(): string {
    return realpath($this->tempProjectScratchDir . '/home');
  }

  public function removeTempProject(): void {
    if (NULL !== $this->tempProjectOriginalHome) {
      if (FALSE === $this->tempProjectOriginalHome) {
        putenv('HOME');
      }
      else {
        putenv('HOME=' . $this->tempProjectOriginalHome);
      }
      $this->tempProjectOriginalHome = NULL;
    }
    if ($this->tempProjectScratchDir) {
      (new Filesystem())->remove($this->tempProjectScratchDir);
      $this->tempProjectScratchDir = NULL;
    }
  }

}
