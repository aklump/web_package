<?php

namespace AKlump\WebPackage\Tests\Helpers;

use AKlump\WebPackage\Helpers\VersionDegree;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\WebPackage\Validator\Constraint\VersionDegree
 */
class VersionDegreeTest extends TestCase {

  public function testMajorIsNotEmpty() {
    $this->assertNotEmpty(VersionDegree::MAJOR);
  }

  public function testMinorIsNotEmpty() {
    $this->assertNotEmpty(VersionDegree::MINOR);
  }

  public function testPatchIsNotEmpty() {
    $this->assertNotEmpty(VersionDegree::PATCH);
  }
}
