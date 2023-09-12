<?php

namespace AKlump\WebPackage\Tests\Traits;

use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\WebPackage\Traits\ImplodeTrait
 */
class ImplodeTraitTest extends TestCase {

  public function dataFortestAndProvider() {
    $tests = [];
    $tests[] = [
      'lorem',
      ['lorem'],
    ];
    $tests[] = [
      'lorem and ipsum',
      ['lorem', 'ipsum'],
    ];
    $tests[] = [
      'do, re and mi',
      ['do', 're', 'mi'],
    ];

    return $tests;
  }

  /**
   * @dataProvider dataFortestAndProvider
   */
  public function testAnd(string $expected, array $subject) {
    $this->assertSame($expected, (new Testable())->and($subject));
  }


  public function dataFortestOrProvider() {
    $tests = [];
    $tests[] = [
      'lorem',
      ['lorem'],
    ];
    $tests[] = [
      'lorem or ipsum',
      ['lorem', 'ipsum'],
    ];
    $tests[] = [
      'do, re or mi',
      ['do', 're', 'mi'],
    ];

    return $tests;
  }

  /**
   * @dataProvider dataFortestOrProvider
   */
  public function testOr(string $expected, array $subject) {
    $this->assertSame($expected, (new Testable())->or($subject));
  }

}

class Testable {

  use \AKlump\WebPackage\Traits\ImplodeTrait;
}
