<?php

namespace AKlump\WebPackage\Tests\Unit\Helpers;

use AKlump\WebPackage\Git\GitProxy;
use AKlump\WebPackage\Helpers\GetBuildNumber;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\WebPackage\Helpers\GetBuildNumber
 */
class GetBuildNumberTest extends TestCase {

  const VERSION_FILE = '.web_package/config.yml';

  /**
   * @param string[] $tags
   *   The tags git reports, newest first.
   * @param array $commits
   *   Keys are the refs that are asked about, values are the list of files that
   *   commit changed.  Insertion order is the walk from HEAD back to the root.
   */
  private function createGit(array $tags, array $commits): GitProxy {
    $git = $this->createMock(GitProxy::class);
    $git->method('getTagsByCreationDate')->willReturn($tags);
    $git->method('getFilesInCommit')->willReturnCallback(function (string $ref) use ($commits) {
      return $commits[$ref];
    });
    $refs = array_keys($commits);
    $git->method('getParent')->willReturnCallback(function (string $ref) use ($refs) {
      $index = array_search($ref, $refs, TRUE);

      return $refs[$index + 1] ?? '';
    });

    return $git;
  }

  public function testCountsFromTheNewestTagToHead() {
    $git = $this->createGit(['3.3.3', '3.3.2'], ['HEAD' => ['src/a.php']]);
    $git->expects($this->once())->method('countCommits')->with('3.3.3..HEAD')->willReturn(7);
    $this->assertSame(7, (new GetBuildNumber($git, self::VERSION_FILE))());
  }

  public function testCountsEveryCommitWhenThereIsNoTag() {
    $git = $this->createGit([], ['HEAD' => ['src/a.php']]);
    $git->expects($this->once())->method('countCommits')->with('HEAD')->willReturn(12);
    $this->assertSame(12, (new GetBuildNumber($git, self::VERSION_FILE))());
  }

  /**
   * @dataProvider dataFortestTagSelectionProvider
   */
  public function testTagSelection(array $tags, string $prefix, string $expected_range) {
    $git = $this->createGit($tags, ['HEAD' => ['src/a.php']]);
    $git->expects($this->once())->method('countCommits')->with($expected_range)->willReturn(1);
    (new GetBuildNumber($git, self::VERSION_FILE, $prefix))();
  }

  public function dataFortestTagSelectionProvider() {
    $tests = [];
    $tests['bare tag'] = [['1.2.3'], '', '1.2.3..HEAD'];
    $tests['v-prefixed tag keeps its v'] = [['v1.2.3'], '', 'v1.2.3..HEAD'];
    $tests['short tags are versions'] = [['1.2', '1'], '', '1.2..HEAD'];
    $tests['unrelated tags are ignored'] = [['latest', 'nightly', 'release-1', '1.2.3-rc1', '1.2.3.4', '1.2.3'], '', '1.2.3..HEAD'];
    $tests['first match wins, not the highest'] = [['1.0.0', '2.0.0'], '', '1.0.0..HEAD'];
    $tests['prefixed tags need the prefix'] = [['1.2.3', '8.x-1.4.0', '8.x-1.3.0'], '8.x-', '8.x-1.4.0..HEAD'];
    $tests['prefixed tags may have a v'] = [['v8.x-1.4.0'], '8.x-', 'v8.x-1.4.0..HEAD'];
    $tests['bare versions do not match a prefix'] = [['1.2.3'], '8.x-', 'HEAD'];
    $tests['the prefix is not a pattern'] = [['8Ax-1.4.0', '8.x-1.3.0'], '8.x-', '8.x-1.3.0..HEAD'];

    return $tests;
  }

  public function testCommitsThatOnlyChangedTheVersionFileAreSkipped() {
    $git = $this->createGit(['1.0.0'], [
      'HEAD' => [self::VERSION_FILE],
      'bbb' => [self::VERSION_FILE],
      'ccc' => ['src/a.php'],
      'ddd' => ['src/b.php'],
    ]);
    $git->expects($this->once())->method('countCommits')->with('1.0.0..ccc')->willReturn(2);
    $this->assertSame(2, (new GetBuildNumber($git, self::VERSION_FILE))());
  }

  /**
   * Only a commit that changed the version file and nothing else is skipped.
   */
  public function testACommitThatChangedOtherFilesToo() {
    $git = $this->createGit(['1.0.0'], [
      'HEAD' => [self::VERSION_FILE, 'CHANGELOG.md'],
      'bbb' => [self::VERSION_FILE],
    ]);
    $git->expects($this->once())->method('countCommits')->with('1.0.0..HEAD')->willReturn(3);
    $this->assertSame(3, (new GetBuildNumber($git, self::VERSION_FILE))());
  }

  public function testACommitWithNoChangedFilesIsNotSkipped() {
    $git = $this->createGit([], ['HEAD' => [], 'bbb' => [self::VERSION_FILE]]);
    $git->expects($this->once())->method('countCommits')->with('HEAD')->willReturn(2);
    $this->assertSame(2, (new GetBuildNumber($git, self::VERSION_FILE))());
  }

  public function testTheWalkStopsAtTheRootCommit() {
    $git = $this->createGit([], [
      'HEAD' => [self::VERSION_FILE],
      'root' => [self::VERSION_FILE],
    ]);
    $git->expects($this->once())->method('countCommits')->with('root')->willReturn(1);
    $this->assertSame(1, (new GetBuildNumber($git, self::VERSION_FILE))());
  }

}
