<?php

namespace AKlump\WebPackage\Tests\VersionScribes;

use AKlump\WebPackage\Tests\Traits\WriteTestTrait;
use AKlump\WebPackage\VersionScribeInterface;
use AKlump\WebPackage\VersionScribes\IniFile;
use PHPUnit\Framework\TestCase;
use z4kn4fein\SemVer\Inc;

/**
 * @covers \AKlump\WebPackage\VersionScribes\IniFile
 */
class IniFileTest extends TestCase {

  use WriteTestTrait;

  public function testWriteReplacesVersionInExistingFile() {
    $old = $this->getVersion();
    $new = $old->inc(Inc::MINOR);
    $this->unlink('ini');
    $path = $this->getPath('ini');
    $scribe = new IniFile($path);
    $this->assertTrue($scribe->write($old));
    $this->assertTrue($scribe->write($new));
    $this->assertSame((string) $new, (string) $scribe->read());
    $this->unlink('ini');
  }

  public function testWriteCreatesFileAndWritesVersion() {
    $version = $this->getVersion();
    $this->unlink('ini');
    $path = $this->getPath('ini');
    $scribe = new IniFile($path);
    $this->assertTrue($scribe->write($version));
    $this->assertSame((string) $version, (string) $scribe->read());
    $this->unlink('ini');
  }

  public function testIniFileReturnsValueOfVersionKey() {
    $scribe = new IniFile(__DIR__ . '/../files/file.ini');
    $version = $scribe->read();
    $this->assertSame('2.3.4', (string) $version);
  }

  public function testIniFileWithoutVersionReturnsDefaul() {
    $scribe = new IniFile(__DIR__ . '/../files/file2.ini');
    $version = $scribe->read();
    $this->assertSame(VersionScribeInterface::DEFAULT, (string) $version);
  }

}
