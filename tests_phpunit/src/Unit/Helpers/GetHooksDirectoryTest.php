<?php

namespace AKlump\WebPackage\Tests\Unit\Helpers;

use AKlump\WebPackage\Helpers\GetHooksDirectory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \AKlump\WebPackage\Helpers\GetHooksDirectory
 */
class GetHooksDirectoryTest extends TestCase {

  public function testDirectoryIsBuiltFromTheInjectedRoot() {
    $this->assertSame('/path/to/project/.web_package/hooks/build/', (new GetHooksDirectory('/path/to/project'))('build'));
    $this->assertSame('/other/.web_package/hooks/dev/', (new GetHooksDirectory('/other'))('dev'));
  }

  public function testEmptyHookTypeThrows() {
    $this->expectException(\InvalidArgumentException::class);
    (new GetHooksDirectory('/path/to/project'))('');
  }

}
