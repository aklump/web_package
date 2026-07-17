<?php

namespace AKlump\WebPackage\Upgrade;

use AKlump\WebPackage\Helpers\GetDerivativeComposerName;
use Jawira\CaseConverter\CaseConverter;

class ComposerJson {

  /**
   * @param string $composer_json_path
   *
   * @return void
   *
   * @throws \RuntimeException When it fails to initialize composer.
   */
  public function execute(string $composer_json_path): void {
    $directory = dirname($composer_json_path);
    $derivate_name = (new GetDerivativeComposerName())();
    $command = sprintf('cd "%s" && composer init -n --name="%s"', $directory, $derivate_name);
    system($command, $status);
    if ($status !== 0) {
      throw new \RuntimeException(sprintf('Could not initialize composer: %s', $command));
    }
    $composer_json = json_decode(file_get_contents($composer_json_path));
    $camel_case = explode('/', $derivate_name, 2)[1];
    $camel_case = (new CaseConverter())->convert($camel_case)->toPascal();
    $composer_json->autoload = [];
    $composer_json->autoload['psr-4']['AKlump\\WebPackage\\User\\'] = 'src/';
    $composer_json->autoload['psr-4']['AKlump\\' . $camel_case . '\\'] = '../src/';
    file_put_contents($composer_json_path, json_encode($composer_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);

    $command = sprintf('cd "%s" && composer dump-autoload', $directory);
    system($command, $status);
    if ($status !== 0) {
      throw new \RuntimeException(sprintf('Could not dump composer autoload: %s', $command));
    }
  }
}
