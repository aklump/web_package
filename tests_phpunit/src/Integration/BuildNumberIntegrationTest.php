<?php

namespace AKlump\WebPackage\Tests\Integration;

use AKlump\WebPackage\Config\Config;
use AKlump\WebPackage\Git\GitProxy;
use AKlump\WebPackage\Helpers\GetBuildNumber;
use AKlump\WebPackage\Helpers\StampVersion;
use AKlump\WebPackage\VersionScribes\Yaml;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Run the build-number logic against the history of a real git repository.
 *
 * @group integration
 * @covers \AKlump\WebPackage\Helpers\GetBuildNumber
 * @covers \AKlump\WebPackage\Helpers\StampVersion
 * @covers \AKlump\WebPackage\Git\GitProxy
 * @uses \AKlump\WebPackage\Helpers\GetCurrentVersion
 * @uses \AKlump\WebPackage\Model\Version
 * @uses \AKlump\WebPackage\Traits\HasConfigTrait
 * @uses \AKlump\WebPackage\Traits\WriterTrait
 * @uses \AKlump\WebPackage\VersionScribes\Yaml
 */
class BuildNumberIntegrationTest extends TestCase {

  const VERSION_FILE = '.web_package/config.yml';

  /**
   * @var string
   */
  private $repo;

  /**
   * @var string
   */
  private $originalCwd;

  protected function setUp(): void {
    exec('git --version 2>&1', $output, $status);
    if (0 !== $status) {
      $this->markTestSkipped('git is not on the PATH.');
    }
    $this->originalCwd = getcwd();
    $this->repo = sys_get_temp_dir() . '/bump_build_number_' . uniqid('', TRUE);
    mkdir($this->repo);
    $this->repo = realpath($this->repo);
    chdir($this->repo);
    $this->git('init -q');
  }

  protected function tearDown(): void {
    if ($this->originalCwd) {
      chdir($this->originalCwd);
    }
    if ($this->repo) {
      (new Filesystem())->remove($this->repo);
    }
  }

  /**
   * Run git in the repo, without reading anyone's git configuration.
   */
  private function git(string $arguments): void {
    $command = sprintf(
      'git -c user.name=Bump -c user.email=bump@example.com -c commit.gpgsign=false -c tag.gpgsign=false %s 2>&1',
      $arguments
    );
    exec($command, $output, $status);
    $this->assertSame(0, $status, sprintf("%s\n%s", $command, implode("\n", $output)));
  }

  private function commit(string $message, array $files): void {
    foreach ($files as $path => $contents) {
      (new Filesystem())->dumpFile($this->repo . '/' . $path, $contents);
    }
    $this->git('add -A');
    $this->git('commit -q -m ' . escapeshellarg($message));
  }

  private function stamp(): string {
    $scribe = new Yaml($this->repo . '/' . self::VERSION_FILE);
    $config = [
      Config::VERSION_FILE => $this->repo . '/' . self::VERSION_FILE,
      'git_root' => '.',
    ];

    return (new StampVersion($config, $scribe, new GitProxy(), $this->repo))();
  }

  public function testTheBuildNumberCountsCommitsSinceTheTagAndIgnoresVersionOnlyCommits() {
    $git = new GitProxy();
    $this->commit('Initial', [self::VERSION_FILE => "version: \"1.0.0\"\n", 'a.txt' => 'a']);
    $git->tag('1.0.0');
    $git->tag('latest');
    $this->commit('Second', ['a.txt' => 'b']);
    $this->commit('Third', ['b.txt' => 'c']);

    $version_file_from_git_root = self::VERSION_FILE;
    $this->assertSame(2, (new GetBuildNumber($git, $version_file_from_git_root))());

    // Stamp and commit only the version file.
    $this->assertSame('1.0.0+2', $this->stamp());
    $this->assertSame("version: \"1.0.0+2\"\n", file_get_contents($this->repo . '/' . self::VERSION_FILE));
    $this->commit('build: Version 1.0.0+2', [self::VERSION_FILE => file_get_contents($this->repo . '/' . self::VERSION_FILE)]);
    $this->assertSame(['.web_package/config.yml'], $git->getFilesInCommit('HEAD'));

    $this->assertSame(2, (new GetBuildNumber($git, $version_file_from_git_root))());
    $this->assertSame('1.0.0+2', $this->stamp());

    // Only version-only commits at the tip are skipped; once a real commit
    // follows one, it is counted like any other.
    $this->commit('Fourth', ['c.txt' => 'd']);
    $this->assertSame(4, (new GetBuildNumber($git, $version_file_from_git_root))());
    $this->assertSame('1.0.0+4', $this->stamp());
  }

  public function testWithoutATagEveryCommitIsCounted() {
    $git = new GitProxy();
    $this->commit('Initial', [self::VERSION_FILE => "version: \"0.1.0\"\n", 'a.txt' => 'a']);
    $this->commit('Second', ['a.txt' => 'b']);
    $this->assertSame([], $git->getTagsByCreationDate());
    $this->assertSame(2, (new GetBuildNumber($git, self::VERSION_FILE))());
  }

  public function testTheWalkStopsAtAVersionOnlyRootCommit() {
    $git = new GitProxy();
    $this->commit('Initial', [self::VERSION_FILE => "version: \"0.1.0\"\n"]);
    $this->assertSame('', $git->getParent('HEAD'));
    $this->assertSame(1, (new GetBuildNumber($git, self::VERSION_FILE))());
  }

}
