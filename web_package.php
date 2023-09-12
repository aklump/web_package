#!/usr/bin/env php
<?php

use AKlump\WebPackage\Command\InitCommand;
use AKlump\WebPackage\Command\MajorCommand;
use AKlump\WebPackage\Command\MinorCommand;
use AKlump\WebPackage\Command\PatchCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Filesystem\Filesystem;

$autoload = __DIR__ . '/../../../vendor/autoload.php';
if (!is_file($autoload)) {
  $autoload = __DIR__ . '/vendor/autoload.php';
}
require_once $autoload;

$filesystem = new Filesystem();

$app = new Application();
$app->setName('web_package');
$app->setVersion('0.0.1');

$app->add(new InitCommand(
  '.web_package',
  __DIR__ . '/install/template/',
  $filesystem));
$app->add(new MajorCommand());
$app->add(new MinorCommand());
$app->add(new PatchCommand());
$app->run();
