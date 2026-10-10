<?php

namespace AKlump\WebPackage\Tests\Unit\Git;

use AKlump\WebPackage\Git\GitProxy;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\WebPackage\Git\GitProxy
 */
class GitProxyTest extends TestCase {

  /**
   * Get a GitProxy whose shell calls are replaced.
   *
   * @param string[] $methods
   *   The ShellCommandTrait methods to replace.
   */
  private function createProxy(array $methods): GitProxy {
    return $this->getMockBuilder(GitProxy::class)
      ->onlyMethods($methods)
      ->getMock();
  }

  public function testTag() {
    $git = $this->createProxy(['system']);
    $git->expects($this->once())
      ->method('system')
      ->with("git tag '3.4.0'")
      ->willReturn('tagged');
    $this->assertSame('tagged', $git->tag('3.4.0'));
  }

  public function testDeleteBranch() {
    $git = $this->createProxy(['system']);
    $git->expects($this->once())
      ->method('system')
      ->with("git branch -d 'release--1.2.0'")
      ->willReturn('deleted');
    $this->assertSame('deleted', $git->deleteBranch('release--1.2.0'));
  }

  public function testCreateBranch() {
    $git = $this->createProxy(['system']);
    $git->expects($this->once())
      ->method('system')
      ->with("git checkout -b 'release--1.2.0' 'develop'")
      ->willReturn('created');
    $this->assertSame('created', $git->createBranch('release--1.2.0', 'develop'));
  }

  public function testMergeBranchChecksOutTheTargetThenMergesWithoutFastForward() {
    $git = $this->createProxy(['system']);
    $git->expects($this->exactly(2))
      ->method('system')
      ->withConsecutive(
        ["git checkout 'main'"],
        ["git merge --no-ff 'release--1.2.0' -m 'Merge branch release--1.2.0'"]
      );
    $git->mergeBranch('main', 'release--1.2.0');
  }

  public function testCommitFile() {
    $git = $this->createProxy(['system']);
    $git->expects($this->once())
      ->method('system')
      ->with("git commit 'config.yml' -m 'Bumped version number to 1.2.0'");
    $git->commitFile('config.yml', 'Bumped version number to 1.2.0');
  }

  public function testPush() {
    $git = $this->createProxy(['system']);
    $git->expects($this->once())
      ->method('system')
      ->with("git push origin '3.4.0'")
      ->willReturn('pushed');
    $this->assertSame('pushed', $git->push('3.4.0'));
  }

  /**
   * An argument with a space and quotes can't break out of its quoting.
   */
  public function testArgumentsWithSpacesAndQuotesAreEscaped() {
    $git = $this->createProxy(['system']);
    $dangerous = "it's a \"test\"; rm -rf /";
    $git->expects($this->once())
      ->method('system')
      ->with('git commit ' . escapeshellarg('my file.txt') . ' -m ' . escapeshellarg($dangerous));
    $git->commitFile('my file.txt', $dangerous);

    $this->assertSame("'it'\\''s a \"test\"; rm -rf /'", escapeshellarg($dangerous));
  }

  public function testGetUncommittedFilesReturnsOneEntryPerLine() {
    $git = $this->createProxy(['exec']);
    $git->expects($this->once())
      ->method('exec')
      ->with('git status --short')
      ->willReturnCallback(function (string $command, array &$output = []) {
        $output = [' M src/foo.php', '?? bar.txt'];

        return '?? bar.txt';
      });
    $this->assertSame([' M src/foo.php', '?? bar.txt'], $git->getUncommittedFiles());
  }

  public function testGetUncommittedFilesIsEmptyWhenTheTreeIsClean() {
    $git = $this->createProxy(['exec']);
    $git->method('exec')->willReturn('');
    $this->assertSame([], $git->getUncommittedFiles());
  }

  public function testGetUncommittedFilesIsEmptyWhenNotAGitRepository() {
    $git = $this->createProxy(['exec']);
    $git->method('exec')->willThrowException(new \RuntimeException('Failed: git status --short'));
    $this->assertSame([], $git->getUncommittedFiles());
  }

}
