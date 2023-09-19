<?php

namespace AKlump\WebPackage\Tests\Helpers;

use AKlump\WebPackage\Model\Context;

class StashTest extends \PHPUnit\Framework\TestCase {

  use \AKlump\WebPackage\Tests\WriteTestTrait;

  public function testCanWriteAndReadVersion() {
    $context = $this->createConfiguredMock(Context::class, [
      'getRootPath' => dirname($this->getPath('')),
    ]);
    $stash = new \AKlump\WebPackage\Helpers\Stash($context);
    $stash->write('lorem', 'ipsum');
    $this->assertSame('ipsum', $stash->read('lorem'));
  }

}
