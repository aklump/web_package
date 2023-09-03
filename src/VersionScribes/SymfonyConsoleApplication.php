<?php

namespace AKlump\WebPackage\VersionScribes;

use AKlump\WebPackage\Traits\ReaderTrait;
use AKlump\WebPackage\VersionScribeInterface;
use z4kn4fein\SemVer\Version;

class SymfonyConsoleApplication implements VersionScribeInterface {

  use ReaderTrait;

  private $source;

  public function __construct(string $source) {
    $this->source = $source;
  }

  public function read(): Version {
    $contents = file_get_contents($this->source);
    preg_match("/setVersion\('(.+?)'\)/", $contents, $matches);

    return $this->getVersion($matches[1] ?? '');
  }

  public function write(Version $version): bool {
    // TODO: Implement write() method.

    $template = '#!/usr/bin/env php' . PHP_EOL;
    $template .= '<?php' . PHP_EOL;
    $template .= '$app = new Symfony\Component\Console\Application();' . PHP_EOL;
    $template .= sprintf("\$app->setVersion('%s');", $version) . PHP_EOL . PHP_EOL;

    return file_put_contents($this->source, $template);
  }
}
