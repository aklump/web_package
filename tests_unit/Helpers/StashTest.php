<?php

namespace AKlump\WebPackage\Tests\Helpers;

use AKlump\WebPackage\Model\Context;
use AKlump\WebPackage\Tests\WriteTestTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\WebPackage\Helpers\Stash
 */
class StashTest extends TestCase {

  use WriteTestTrait;

  public function testCanWriteAndReadVersion() {
    $context = $this->createConfiguredMock(Context::class, [
      'getRootPath' => dirname($this->getPath('')),
    ]);
    $stash = new \AKlump\WebPackage\Helpers\Stash($context);
    $stash->write('lorem', 'ipsum');
    $this->assertSame('ipsum', $stash->read('lorem'));
  }

}
