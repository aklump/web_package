<?php

namespace AKlump\WebPackage\VersionScribes;

use AKlump\WebPackage\Traits\WriterTrait;
use AKlump\WebPackage\VersionScribeInterface;
use AKlump\WebPackage\Model\Version;

class SymfonyConsoleApplication implements VersionScribeInterface {

  use WriterTrait;

  const REGEX = '/(\->setVersion\([\'"])(.+?)([\'"]\))/i';
  private $source;

  public function __construct(string $source) {
    $this->source = $source;
  }


  public function getFilepath(): string {
    return $this->source;
  }
  public function read(): string {
    if (file_exists($this->source)) {
      $contents = file_get_contents($this->source);
      preg_match(self::REGEX, $contents, $matches);
    }

    if (empty($matches[2])) {
      return '';
    }

    return $matches[2];
  }

  public function write(string $version): bool {
    if (file_exists($this->source)) {
      return $this->regexReplaceVersionInFile($this->source, self::REGEX, $this->read(), $version);
    }

    $template = '#!/usr/bin/env php' . PHP_EOL;
    $template .= '<?php' . PHP_EOL;
    $template .= '$app = new Symfony\Component\Console\Application();' . PHP_EOL;
    $template .= sprintf("\$app->setVersion('%s');", $version) . PHP_EOL . PHP_EOL;

    return file_put_contents($this->source, $template);
  }

}
