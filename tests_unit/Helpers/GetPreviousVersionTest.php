<?php

namespace AKlump\WebPackage\Tests\Helpers;

use AKlump\WebPackage\Helpers\GetPreviousVersion;
use AKlump\WebPackage\Helpers\Stash;
use AKlump\WebPackage\Model\Context;
use AKlump\WebPackage\Tests\WriteTestTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\WebPackage\Helpers\GetPreviousVersion
 */
class GetPreviousVersionTest extends TestCase {

  use WriteTestTrait;

  public function testCanWriteAndReadVersion() {
    $context = $this->createConfiguredMock(Context::class, [
      'getRootPath' => dirname($this->getPath('')),
    ]);
    $stash = new Stash($context);
    $stash->write(GetPreviousVersion::STASH_KEY, '1.2.3');
    $this->assertSame('1.2.3', (new GetPreviousVersion($context))());
  }

}
