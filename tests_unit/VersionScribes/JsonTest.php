<?php

namespace AKlump\WebPackage\Tests\VersionScribes;

use AKlump\WebPackage\Tests\Traits\WriteTestTrait;
use AKlump\WebPackage\VersionScribeInterface;
use AKlump\WebPackage\VersionScribes\Json;
use PHPUnit\Framework\TestCase;
use z4kn4fein\SemVer\Inc;

/**
 * @covers \AKlump\WebPackage\VersionScribes\Json
 */
class JsonTest extends TestCase {

  use WriteTestTrait;

  public function testWriteReplacesVersionInExistingFile() {
    $old = $this->getVersion();
    $new = $old->inc(Inc::MINOR);
    $this->unlink('json');
    $path = $this->getPath('json');
    $scribe = new Json($path);
    $this->assertTrue($scribe->write($old));
    $this->assertTrue($scribe->write($new));
    $this->assertSame((string) $new, (string) $scribe->read());
    $this->unlink('json');
  }

  public function testWriteCreatesFileAndWritesVersion() {
    $version = $this->getVersion();
    $this->unlink('json');
    $path = $this->getPath('json');
    $scribe = new Json($path);
    $this->assertTrue($scribe->write($version));
    $this->assertSame((string) $version, (string) $scribe->read());
    $this->unlink('json');
  }

  public function testJsonReturnsValueOfVersionKey() {
    $scribe = new Json(__DIR__ . '/../files/composer.json');
    $version = $scribe->read();
    $this->assertSame('1.2.3', (string) $version);
  }

  public function testJsonWithoutVersionReturnsDefaul() {
    $scribe = new Json(__DIR__ . '/../files/composer2.json');
    $version = $scribe->read();
    $this->assertSame(VersionScribeInterface::DEFAULT, (string) $version);
  }

}
