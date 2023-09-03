<?php

namespace AKlump\WebPackage\Tests\VersionScribes;

use AKlump\WebPackage\Tests\Traits\WriteTestTrait;
use AKlump\WebPackage\VersionScribes\Text;
use PHPUnit\Framework\TestCase;
use z4kn4fein\SemVer\Inc;
use z4kn4fein\SemVer\Version;

/**
 * @covers \AKlump\WebPackage\VersionScribes\Text;
 */
class TextTest extends TestCase {

  use WriteTestTrait;

  public function testWriteReplacesVersionInExistingFile() {
    $path = $this->getPath('txt');
    copy(__DIR__ . '/../files/version.txt', $path);
    $expected_size = $this->filesize($path);
    $scribe = new Text($path);
    $old = Version::parse($scribe->read(), FALSE);
    $new = $old->inc(Inc::PATCH);
    $this->assertTrue($scribe->write($new));
    $this->assertTrue($new->isEqual(Version::parse($scribe->read(), FALSE)));
    $this->assertSame($expected_size, $this->filesize($path));
    $this->unlink('txt');
  }

  public function testWriteCreatesFileAndWritesVersion() {
    $version = $this->getVersion();
    $this->unlink('txt');
    $path = $this->getPath('txt');
    $scribe = new Text($path);
    $this->assertTrue($scribe->write($version));
    $this->assertSame((string) $version, $scribe->read());
    $this->unlink('txt');
  }

  public function testTextReturnsValueOfVersionKey() {
    $scribe = new Text(__DIR__ . '/../files/version.txt');
    $version = $scribe->read();
    $this->assertSame('4.3.5', $version);
  }

}
