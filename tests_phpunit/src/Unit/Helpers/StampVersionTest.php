<?php

namespace AKlump\WebPackage\Tests\Unit\Helpers;

use AKlump\WebPackage\Config\Config;
use AKlump\WebPackage\Git\GitProxy;
use AKlump\WebPackage\Helpers\StampVersion;
use AKlump\WebPackage\VersionScribeInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\WebPackage\Helpers\StampVersion
 * @uses \AKlump\WebPackage\Helpers\GetBuildNumber
 * @uses \AKlump\WebPackage\Helpers\GetCurrentVersion
 * @uses \AKlump\WebPackage\Model\Version
 */
class StampVersionTest extends TestCase {

  const ROOT = '/projects/example';

  private function createScribe(string $current, bool $write_succeeds = TRUE): VersionScribeInterface {
    $scribe = $this->createMock(VersionScribeInterface::class);
    $scribe->method('read')->willReturn($current);
    $scribe->method('getFilepath')->willReturn(self::ROOT . '/.web_package/config.yml');
    $scribe->method('write')->willReturn($write_succeeds);

    return $scribe;
  }

  /**
   * @param string[] $tags
   * @param string[] $head_files
   *   The files changed by HEAD.
   */
  private function createGit(array $tags, array $head_files, int $count): GitProxy {
    $git = $this->createMock(GitProxy::class);
    $git->method('getTagsByCreationDate')->willReturn($tags);
    $git->method('getFilesInCommit')->willReturn($head_files);
    $git->method('countCommits')->willReturn($count);

    return $git;
  }

  public function testStampsABareCore() {
    $scribe = $this->createScribe('3.3.3');
    $scribe->expects($this->once())->method('write')->with('3.3.3+17')->willReturn(TRUE);
    $git = $this->createGit(['3.3.3'], ['src/a.php'], 17);
    $config = [Config::VERSION_FILE => self::ROOT . '/.web_package/config.yml', 'git_root' => '.'];
    $this->assertSame('3.3.3+17', (new StampVersion($config, $scribe, $git, self::ROOT))());
  }

  public function testReplacesAnExistingBuildNumber() {
    $scribe = $this->createScribe('3.3.3+17');
    $scribe->expects($this->once())->method('write')->with('3.3.3+21')->willReturn(TRUE);
    $git = $this->createGit(['3.3.3'], ['src/a.php'], 21);
    $config = [Config::VERSION_FILE => self::ROOT . '/.web_package/config.yml'];
    $this->assertSame('3.3.3+21', (new StampVersion($config, $scribe, $git, self::ROOT))());
  }

  public function testTheInitialVersionIsStampedWhenTheFileHasNone() {
    $scribe = $this->createScribe('');
    $scribe->expects($this->once())->method('write')->with('1.0.0+4')->willReturn(TRUE);
    $git = $this->createGit([], ['src/a.php'], 4);
    $config = [Config::INITIAL_VERSION => '1.0.0'];
    $this->assertSame('1.0.0+4', (new StampVersion($config, $scribe, $git, self::ROOT))());
  }

  public function testThePrefixLimitsWhichTagsCount() {
    $scribe = $this->createScribe('8.x-1.2.0');
    $git = $this->createMock(GitProxy::class);
    $git->method('getTagsByCreationDate')->willReturn(['2.0.0', '8.x-1.1.0']);
    $git->method('getFilesInCommit')->willReturn(['src/a.php']);
    $git->expects($this->once())->method('countCommits')->with('8.x-1.1.0..HEAD')->willReturn(5);
    $scribe->expects($this->once())->method('write')->with('8.x-1.2.0+5')->willReturn(TRUE);
    (new StampVersion([], $scribe, $git, self::ROOT))();
  }

  /**
   * @dataProvider dataFortestVersionFileIsRelativeToTheGitRootProvider
   */
  public function testVersionFileIsRelativeToTheGitRoot(array $config, string $root, string $version_only_file) {
    // When HEAD changed only the version file, the walk must go past it; that
    // only happens if the path was made relative to the git root correctly.
    $git = $this->createMock(GitProxy::class);
    $git->method('getTagsByCreationDate')->willReturn(['1.0.0']);
    $git->method('getFilesInCommit')->willReturnMap([
      ['HEAD', [$version_only_file]],
      ['parent', ['src/a.php']],
    ]);
    $git->method('getParent')->with('HEAD')->willReturn('parent');
    $git->expects($this->once())->method('countCommits')->with('1.0.0..parent')->willReturn(9);
    $scribe = $this->createScribe('1.0.0');
    $scribe->expects($this->once())->method('write')->with('1.0.0+9')->willReturn(TRUE);
    (new StampVersion($config, $scribe, $git, $root))();
  }

  public function dataFortestVersionFileIsRelativeToTheGitRootProvider() {
    $tests = [];
    $tests['git root is the project root'] = [
      [Config::VERSION_FILE => '/p/.web_package/config.yml', 'git_root' => '.'],
      '/p',
      '.web_package/config.yml',
    ];
    $tests['git root is above the project'] = [
      [Config::VERSION_FILE => '/p/app/.web_package/config.yml', 'git_root' => '..'],
      '/p/app',
      'app/.web_package/config.yml',
    ];
    $tests['a relative version file'] = [
      [Config::VERSION_FILE => 'version.txt', 'git_root' => '..'],
      '/p/app',
      'app/version.txt',
    ];
    $tests['defaults when the config has nothing'] = [
      [],
      '/p',
      '.web_package/config.yml',
    ];

    return $tests;
  }

  public function testFailureToWriteThrows() {
    $scribe = $this->createScribe('3.3.3', FALSE);
    $git = $this->createGit(['3.3.3'], ['src/a.php'], 2);
    $this->expectException(\RuntimeException::class);
    $this->expectExceptionMessage('Failed to write the build number to /projects/example/.web_package/config.yml');
    (new StampVersion([], $scribe, $git, self::ROOT))();
  }

}
