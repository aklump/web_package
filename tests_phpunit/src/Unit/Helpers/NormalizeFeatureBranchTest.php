<?php

namespace AKlump\WebPackage\Tests\Unit\Helpers;

use AKlump\WebPackage\Helpers\NormalizeFeatureBranch;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\WebPackage\Helpers\NormalizeFeatureBranch
 * @uses \AKlump\WebPackage\Helpers\CombineBranchNameParts
 */
class NormalizeFeatureBranchTest extends TestCase {

  public function dataFortestInvokeProvider() {
    $tests = [];
    $tests[] = [
      ['foo_bar', ''],
      'feature--foo-bar',
    ];
    $tests[] = [
      [
        'foo_bar',
        'Aaron Klump',
      ],
      'aaron-klump--feature--foo-bar',
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
