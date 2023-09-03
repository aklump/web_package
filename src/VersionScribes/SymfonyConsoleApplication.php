<?php

namespace AKlump\WebPackage\VersionScribes;

use AKlump\WebPackage\Traits\WriterTrait;
use AKlump\WebPackage\VersionScribeInterface;
use z4kn4fein\SemVer\Version;

class SymfonyConsoleApplication implements VersionScribeInterface {

  use WriterTrait;

  const REGEX = '/(\->setVersion\([\'"])(.+?)([\'"]\))/i';

  private $source;

  public function __construct(string $source) {
    $this->source = $source;
  }

  public function read(): string {
    if (file_exists($this->source)) {
      $contents = file_get_contents($this->source);
      preg_match(self::REGEX, $contents, $matches);
    }

    return $matches[2] ?? VersionScribeInterface::DEFAULT;
  }

  public function write(Version $version): bool {
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
