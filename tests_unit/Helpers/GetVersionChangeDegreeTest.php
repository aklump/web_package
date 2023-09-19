<?php

namespace AKlump\WebPackage\Tests\Helpers;

use AKlump\WebPackage\Helpers\GetVersionChangeDegree;
use AKlump\WebPackage\Helpers\VersionDegree;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\WebPackage\Helpers\GetVersionChangeDegree
 */
class GetVersionChangeDegreeTest extends TestCase {

  public function dataFortestInvokeProvider() {
    $tests = [];
    $tests[] = [
      NULL,
      '1.0.1',
      '1.0.1',
    ];
    $tests[] = [
      VersionDegree::MAJOR,
      '1.0.1',
      '2.0.1',
    ];
    $tests[] = [
      VersionDegree::MINOR,
      '1.0.1',
      '1.1.1',
    ];
    $tests[] = [
      VersionDegree::PATCH,
      '1.0.1',
      '1.0.3',
    ];

    return $tests;
  }

  /**
   * @dataProvider dataFortestInvokeProvider
   */
  public function testInvoke($expected, $a, $b) {
    $this->assertSame($expected, (new GetVersionChangeDegree())($a, $b));
  }

}
