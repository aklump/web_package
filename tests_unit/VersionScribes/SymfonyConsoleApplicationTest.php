<?php

namespace AKlump\WebPackage\Tests\VersionScribes;

use AKlump\WebPackage\Tests\Traits\WriteTestTrait;
use AKlump\WebPackage\VersionScribes\SymfonyConsoleApplication;
use PHPUnit\Framework\TestCase;
use z4kn4fein\SemVer\Inc;
use z4kn4fein\SemVer\Version;

/**
 * @covers \AKlump\WebPackage\VersionScribes\SymfonyConsoleApplication
 */
class SymfonyConsoleApplicationTest extends TestCase {

  use WriteTestTrait;

  public function testWriteReplacesVersionInExistingFile() {
    $path = $this->getPath('php');
    copy(__DIR__ . '/../files/symfonyapp.php', $path);
    $expected_size = $this->filesize($path);
    $scribe = new SymfonyConsoleApplication($path);
    $old = Version::parse($scribe->read(), FALSE);
    $new = $old->inc(Inc::PATCH);
    $this->assertTrue($scribe->write($new));
    $this->assertTrue($new->isEqual(Version::parse($scribe->read(), FALSE)));
    $this->assertSame($expected_size, $this->filesize($path));
    $this->unlink('php');
  }

  public function testWriteCreatesFileAndWritesVersion() {
    $version = $this->getVersion();
    $this->unlink('php');
    $path = $this->getPath('php');
    $scribe = new SymfonyConsoleApplication($path);
    $this->assertTrue($scribe->write($version));
    $this->assertSame((string) $version, $scribe->read());
    $this->unlink('php');
  }

  public function testSymfonyConsoleApplicationReturnsValueOfVersionKey() {
    $scribe = new SymfonyConsoleApplication(__DIR__ . '/../files/symfonyapp.php');
    $version = $scribe->read();
    $this->assertSame('4.5.6', $version);
  }

}
