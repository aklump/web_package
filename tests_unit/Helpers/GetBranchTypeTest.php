<?php

namespace AKlump\WebPackage\Tests\Helpers;

use AKlump\WebPackage\Helpers\GetBranchType;
use AKlump\WebPackage\Model\GitFlow;
use AKlump\WebPackage\Tests\TestingTraits\TestWithConfigTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers GetBranchType
 */
class GetBranchTypeTest extends TestCase {

  use TestWithConfigTrait;

  public function dataFortestInvokeProvider() {
    $tests = [];
    $tests[] = [
      GitFlow::FEATURE,
      [],
      'aaronklump_feature_gop1234',
    ];
    $tests[] = [
      GitFlow::MASTER,
      ['master' => 'main'],
      'main',
    ];
    $tests[] = [
      GitFlow::DEVELOP,
      ['develop' => 'dev'],
      'dev',
    ];
    $tests[] = [
      GitFlow::HOTFIX,
      [],
      'hotfix-1.2.3',
    ];
    $tests[] = [
      GitFlow::RELEASE,
      [],
      'release-1.2.3',
    ];


    return $tests;
  }

  /**
   * @dataProvider dataFortestInvokeProvider
   */
  public function testInvoke($expected, $config, $branch_name) {
    $type = (new GetBranchType($config))($branch_name);
    $this->assertSame($expected, $type);
  }

}
