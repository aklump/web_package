#!/usr/bin/env php
<?php

use AKlump\WebPackage\Command\BuildCommand;
use AKlump\WebPackage\Command\DevCommand;
use AKlump\WebPackage\Command\DoneCommand;
use AKlump\WebPackage\Command\FeatureCommand;
use AKlump\WebPackage\Command\HotfixCommand;
use AKlump\WebPackage\Command\InitCommand;
use AKlump\WebPackage\Command\MajorCommand;
use AKlump\WebPackage\Command\MinorCommand;
use AKlump\WebPackage\Command\PatchCommand;
use AKlump\WebPackage\Command\ReleaseCommand;
use AKlump\WebPackage\Command\UnBuildCommand;
use AKlump\WebPackage\Command\VersionCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Filesystem\Filesystem;

$autoload = __DIR__ . '/../../../vendor/autoload.php';
if (!is_file($autoload)) {
  $autoload = __DIR__ . '/vendor/autoload.php';
}
require_once $autoload;

$filesystem = new Filesystem();

const WEB_PACKAGE_ROOT = __DIR__;

$app = new Application();
$app->setName('web_package');
$app->setVersion('0.0.1');

$app->add(new InitCommand(
  '.web_package',
  __DIR__ . '/install/template/',
  $filesystem));
$app->add(new VersionCommand());
$app->add(new MajorCommand());
$app->add(new MinorCommand());
$app->add(new PatchCommand());
$app->add(new BuildCommand());
$app->add(new UnBuildCommand());
$app->add(new DevCommand());
$app->add(new HotfixCommand());
$app->add(new FeatureCommand());
$app->add(new ReleaseCommand());
$app->add(new DoneCommand());
$app->run();
