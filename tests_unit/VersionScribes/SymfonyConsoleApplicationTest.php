<?php

namespace AKlump\WebPackage\Tests\VersionScribes;

use AKlump\WebPackage\Tests\Traits\WriteTestTrait;
use AKlump\WebPackage\VersionScribes\SymfonyConsoleApplication;
use PHPUnit\Framework\TestCase;
use z4kn4fein\SemVer\Inc;

/**
 * @covers \AKlump\WebPackage\VersionScribes\SymfonyConsoleApplication
 */
class SymfonyConsoleApplicationTest extends TestCase {

  use WriteTestTrait;

  public function testWriteReplacesVersionInExistingFile() {
    $old = $this->getVersion();
    $new = $old->inc(Inc::MINOR);
    $this->unlink('php');
    $path = $this->getPath('php');
    $scribe = new SymfonyConsoleApplication($path);
    $this->assertTrue($scribe->write($old));
    $this->assertTrue($scribe->write($new));
    $this->assertSame((string) $new, (string) $scribe->read());
    $this->unlink('php');
  }

  public function testWriteCreatesFileAndWritesVersion() {
    $version = $this->getVersion();
    $this->unlink('php');
    $path = $this->getPath('php');
    $scribe = new SymfonyConsoleApplication($path);
    $this->assertTrue($scribe->write($version));
    $this->assertSame((string) $version, (string) $scribe->read());
    $this->unlink('php');
  }

  public function testSymfonyConsoleApplicationReturnsValueOfVersionKey() {
    $scribe = new SymfonyConsoleApplication(__DIR__ . '/../files/symfonyapp.php');
    $version = $scribe->read();
    $this->assertSame('4.5.6', (string) $version);
  }

}
