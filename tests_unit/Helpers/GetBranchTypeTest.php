<?php

namespace AKlump\WebPackage\Tests\Helpers;

use AKlump\WebPackage\Helpers\GetBranchType;
use AKlump\WebPackage\Model\GitFlow;
use PHPUnit\Framework\TestCase;
use AKlump\WebPackage\Config\Config;

/**
 * @covers GetBranchType
 */
class GetBranchTypeTest extends TestCase {

  public function dataFortestInvokeProvider() {
    $tests = [];
    $tests[] = [
      GitFlow::FEATURE,
      [],
      'aaronklump_feature_gop1234',
    ];
    $tests[] = [
      GitFlow::MASTER,
      [Config::MAIN_BRANCH => 'main'],
      'main',
    ];
    $tests[] = [
      GitFlow::DEVELOP,
      [Config::DEVELOP_BRANCH => 'dev'],
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
