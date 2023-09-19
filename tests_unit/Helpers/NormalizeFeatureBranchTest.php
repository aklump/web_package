<?php

namespace AKlump\WebPackage\Tests\Helpers;

use AKlump\WebPackage\Helpers\NormalizeFeatureBranch;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\WebPackage\Helpers\NormalizeFeatureBranch
 */
class NormalizeFeatureBranchTest extends TestCase {

  public function dataFortestInvokeProvider() {
    $tests = [];
    $tests[] = [
      ['foo_bar', ''],
      'feature_foo-bar',
    ];
    $tests[] = [
      [
        'foo_bar',
        'Aaron Klump',
      ],
      'aaron-klump_feature_foo-bar',
    ];

    return $tests;
  }

  /**
   * @dataProvider dataFortestInvokeProvider
   */
  public function testInvoke(array $args, string $expected) {
    $normalizer = new NormalizeFeatureBranch();
    $result = call_user_func_array([$normalizer, '__invoke'], $args);
    $this->assertSame($expected, $result);
  }

}
