<?php

namespace AKlump\WebPackage\Tests\VersionScribes;

use AKlump\WebPackage\VersionScribes\Text;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\WebPackage\VersionScribes\Text;
 */
class TextTest extends TestCase {

  public function testTextReturnsValueOfVersionKey() {
    $scribe = new Text(__DIR__ . '/../files/version.txt');
    $version = $scribe->read();
    $this->assertSame('4.3.5', (string) $version);
  }

}
