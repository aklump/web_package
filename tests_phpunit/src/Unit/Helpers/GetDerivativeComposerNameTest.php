<?php

namespace AKlump\WebPackage\Tests\Unit\Helpers;

use AKlump\WebPackage\Helpers\GetDerivativeComposerName;
use AKlump\WebPackage\Tests\TestingTraits\TempProjectTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\WebPackage\Helpers\GetDerivativeComposerName
 * @uses \AKlump\WebPackage\Helpers\GetWebPackageRoot
 */
class GetDerivativeComposerNameTest extends TestCase {

  use TempProjectTrait;

  protected function tearDown(): void {
    $this->removeTempProject();
  }

  public function testNameIsTheApplicationVendorAndTheKebabCasedProjectDirectory() {
    $root = $this->createTempProject();
    $original_cwd = getcwd();
    chdir($root);
    try {
      $name = (new GetDerivativeComposerName())();
    }
    finally {
      chdir($original_cwd);
    }
    $this->assertSame('web-package/temp-project', $name);
  }

}
