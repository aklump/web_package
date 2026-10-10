<?php

namespace AKlump\WebPackage\Tests\Unit\Helpers;

use AKlump\WebPackage\Helpers\GetWebPackageRoot;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\WebPackage\Helpers\GetWebPackageRoot
 */
class GetWebPackageRootTest extends TestCase {

  public function testRootIsTheDirectoryThatHoldsTheApplication() {
    $root = (new GetWebPackageRoot())();
    $this->assertFileExists($root . '/_web_package.php');
    $this->assertFileExists($root . '/includes/hook_runner.sh');
    $this->assertDirectoryExists($root . '/hook_examples');
    $this->assertSame(realpath($root), $root);
  }

}
