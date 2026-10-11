<?php

namespace AKlump\WebPackage\Tests\Unit\Model;

use AKlump\WebPackage\Model\GitFlow;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\WebPackage\Model\GitFlow
 * @uses \AKlump\WebPackage\Helpers\CombineBranchNameParts
 * @uses \AKlump\WebPackage\Helpers\NormalizeFeatureBranch
 */
class GitFlowTest extends TestCase {

  public function testGetBranchNameForMasterAndDevelopPassesThrough() {
    $result = (new Gitflow(GitFlow::MASTER))->getBranchName('foo');
    $this->assertSame('foo', $result);
    $result = (new Gitflow(GitFlow::DEVELOP))->getBranchName('bar');
    $this->assertSame('bar', $result);
  }

  public function testGetBranchNameForUnrecognizedTypePassesThrough() {
    $result = (new GitFlow('lorem'))->getBranchName('foo_bar');
    $this->assertSame('foo_bar', $result);
  }

  public function testGetBranchNameForFeatureIncludesTheLastCommitAuthor() {
    $gitflow = $this->getMockBuilder(GitFlow::class)
      ->setConstructorArgs([GitFlow::FEATURE])
      ->onlyMethods(['exec'])
      ->getMock();
    $gitflow->expects($this->once())
      ->method('exec')
      ->with('git log -1 --pretty=format:"%an"')
      ->willReturn('Aaron Klump');

    $this->assertSame('aaron-klump--feature--foo-bar', $gitflow->getBranchName('foo_bar'));
  }

  public function testGetBranchNameForFeatureOmitsAuthorWhenGitLogThrows() {
    $gitflow = $this->getMockBuilder(GitFlow::class)
      ->setConstructorArgs([GitFlow::FEATURE])
      ->onlyMethods(['exec'])
      ->getMock();
    $gitflow->expects($this->once())
      ->method('exec')
      ->willThrowException(new \RuntimeException('Failed'));

    $this->assertSame('feature--foo-bar', $gitflow->getBranchName('foo_bar'));
  }

  public function dataFortestGetBranchNamePrefixesWithTypeProvider() {
    $tests = [];
    $tests[] = [
      GitFlow::RELEASE,
      '1.2.0',
      'release--1.2.0',
    ];
    $tests[] = [
      GitFlow::HOTFIX,
      '1.2.1',
      'hotfix--1.2.1',
    ];

    return $tests;
  }

  /**
   * @dataProvider dataFortestGetBranchNamePrefixesWithTypeProvider
   */
  public function testGetBranchNamePrefixesWithType(string $type, string $base, string $expected) {
    $this->assertSame($expected, (new GitFlow($type))->getBranchName($base));
  }

  public function dataFortestGetMayBranchOffFromReturnsExpectedProvider() {
    $tests = [];
    $tests[] = [
      GitFlow::MASTER,
      [],
    ];
    $tests[] = [
      GitFlow::DEVELOP,
      [],
    ];
    $tests[] = [
      GitFlow::FEATURE,
      ['deer'],
    ];
    $tests[] = [
      GitFlow::RELEASE,
      ['deer'],
    ];
    $tests[] = [
      GitFlow::HOTFIX,
      ['mouse'],
    ];

    return $tests;
  }

  /**
   * @dataProvider dataFortestGetMayBranchOffFromReturnsExpectedProvider
   */
  public function testGetMayBranchOffFromReturnsExpected(string $type, array $expected) {
    $gitflow = new Gitflow($type, 'mouse', 'deer');
    $this->assertSame($expected, $gitflow->getMayBranchOffFrom());
  }

  public function dataFortestGetMustMergeBackIntoReturnsExpectedProvider() {
    $tests = [];
    $tests[] = [
      GitFlow::MASTER,
      [],
    ];
    $tests[] = [
      GitFlow::DEVELOP,
      [],
    ];
    $tests[] = [
      GitFlow::FEATURE,
      ['deer'],
    ];
    // Develop must come before master, as this determines the merge order.
    $tests[] = [
      GitFlow::RELEASE,
      ['deer', 'mouse'],
    ];
    $tests[] = [
      GitFlow::HOTFIX,
      ['deer', 'mouse'],
    ];

    return $tests;
  }

  /**
   * @dataProvider dataFortestGetMustMergeBackIntoReturnsExpectedProvider
   */
  public function testGetMustMergeBackIntoReturnsExpected(string $type, array $expected) {
    $gitflow = new GitFlow($type, 'mouse', 'deer');
    $this->assertSame($expected, $gitflow->getMustMergeBackInto());
  }

  public function testGetMustMergeBackIntoReturnsUniqueArray() {
    $gitflow = new GitFlow(GitFlow::HOTFIX, 'mouse', 'deer');
    $this->assertCount(2, $gitflow->getMustMergeBackInto());

    $gitflow = new GitFlow(GitFlow::HOTFIX, 'mouse', 'mouse');
    $this->assertCount(1, $gitflow->getMustMergeBackInto());
  }

  public function testGetMustMergeBackIntoRemovesEmptyBranchNames() {
    $gitflow = new GitFlow(GitFlow::FEATURE, 'mouse', '');
    $this->assertSame([], $gitflow->getMustMergeBackInto());

    $gitflow = new GitFlow(GitFlow::RELEASE, 'mouse', '');
    $this->assertSame(['mouse'], array_values($gitflow->getMustMergeBackInto()));
  }

  public function testConstructorDefaultsToMasterAndDevelopBranchNames() {
    $gitflow = new GitFlow(GitFlow::FEATURE);
    $this->assertSame([GitFlow::DEVELOP], $gitflow->getMayBranchOffFrom());
    $this->assertSame([GitFlow::DEVELOP], $gitflow->getMustMergeBackInto());

    $gitflow = new GitFlow(GitFlow::HOTFIX);
    $this->assertSame([GitFlow::MASTER], $gitflow->getMayBranchOffFrom());
    $this->assertSame([
      GitFlow::DEVELOP,
      GitFlow::MASTER,
    ], $gitflow->getMustMergeBackInto());
  }

  public function dataFortestGetMayFinishReturnsFinishableTypesProvider() {
    $tests = [];
    $tests[] = [GitFlow::MASTER];
    $tests[] = [GitFlow::DEVELOP];
    $tests[] = [GitFlow::FEATURE];
    $tests[] = [GitFlow::RELEASE];
    $tests[] = [GitFlow::HOTFIX];

    return $tests;
  }

  /**
   * @dataProvider dataFortestGetMayFinishReturnsFinishableTypesProvider
   */
  public function testGetMayFinishReturnsFinishableTypes(string $type) {
    $gitflow = new GitFlow($type, 'mouse', 'deer');
    $this->assertSame([
      GitFlow::FEATURE,
      GitFlow::RELEASE,
      GitFlow::HOTFIX,
    ], $gitflow->getMayFinish());
  }

}
