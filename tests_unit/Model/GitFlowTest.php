<?php

namespace AKlump\WebPackage\Tests\Model;

use AKlump\WebPackage\Model\GitFlow;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\WebPackage\Model\GitFlow
 */
class GitFlowTest extends TestCase {

  public function testGetBranchNameForMasterAndDevelopPassesThrough() {
    $result = (new Gitflow(GitFlow::MASTER))->getBranchName('foo');
    $this->assertSame('foo', $result);
    $result = (new Gitflow(GitFlow::DEVELOP))->getBranchName('bar');
    $this->assertSame('bar', $result);
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

  public function testGetMustMergeBackIntoReturnsUniqueArray() {
    $gitflow = new GitFlow(GitFlow::HOTFIX, 'mouse', 'deer');
    $this->assertCount(2, $gitflow->getMustMergeBackInto());

    $gitflow = new GitFlow(GitFlow::HOTFIX, 'mouse', 'mouse');
    $this->assertCount(1, $gitflow->getMustMergeBackInto());
  }

}
