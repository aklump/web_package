#!/usr/bin/env php
<?php

use AKlump\WebPackage\Command\BuildCommand;
use AKlump\WebPackage\Command\ConfigCommand;
use AKlump\WebPackage\Command\DevCommand;
use AKlump\WebPackage\Command\DoneCommand;
use AKlump\WebPackage\Command\FeatureCommand;
use AKlump\WebPackage\Command\HookLibCommand;
use AKlump\WebPackage\Command\HotfixCommand;
use AKlump\WebPackage\Command\InitCommand;
use AKlump\WebPackage\Command\MajorCommand;
use AKlump\WebPackage\Command\MinorCommand;
use AKlump\WebPackage\Command\PatchCommand;
use AKlump\WebPackage\Command\PublishCommand;
use AKlump\WebPackage\Command\ReleaseCommand;
use AKlump\WebPackage\Command\UnBuildCommand;
use AKlump\WebPackage\Command\UpgradeCommand;
use AKlump\WebPackage\Command\VersionCommand;
use AKlump\WebPackage\Config\LoadConfig;
use AKlump\WebPackage\Git\GitProxy;
use AKlump\WebPackage\Helpers\GetRootPath;
use AKlump\WebPackage\Model\Context;
use AKlump\WebPackage\VersionScribeFactory;
use League\Container\Container;
use Symfony\Component\Console\Application;
use Symfony\Component\Filesystem\Filesystem;

$autoload = __DIR__ . '/../../../vendor/autoload.php';
if (!is_file($autoload)) {
  $autoload = __DIR__ . '/vendor/autoload.php';
}
$loader = require_once $autoload;

$filesystem = new Filesystem();

const WEB_PACKAGE_ROOT = __DIR__;
const INSTALL_DIR = '.web_package';

// This is the path where INSTALL_DIR is located, or will be created.
define("ROOT_PATH", (new GetRootPath())(getcwd()));

// Automatic Composer autoloader support.
$user_autoload = ROOT_PATH . '/' . INSTALL_DIR . '/vendor/autoload.php';
if (file_exists($user_autoload)) {
  require_once $user_autoload;
}

// Autowire PSR-4 namespace.
$user_src_directory = ROOT_PATH . '/' . INSTALL_DIR . '/src';
if (file_exists($user_src_directory)) {
  $loader->addPsr4('\AKlump\WebPackage\User\\', [
    $user_src_directory,
  ]);
  // This is legacy support.
  $loader->addPsr4('\AKlump\WebPackage\\', [
    $user_src_directory,
  ]);
}

$app = new Application();
$app->setName('web_package');
$app->setVersion('0.0.1');

$container = new Container();
$container->add('config.loader', LoadConfig::class);
$container->add('context', Context::class)
  ->addArguments(['config.loader']);
$container->add('scribe.factory', VersionScribeFactory::class)
  ->addArguments(['config.loader', 'context']);
$container->add('git', GitProxy::class);

$app->add(new InitCommand(
  $container,
  INSTALL_DIR,
  __DIR__ . '/install/template/',
  $filesystem
));

// TODO Remove all create() methods for __construct().
$app->add(new BuildCommand($container));
$app->add(new PublishCommand($container));
$app->add(new ConfigCommand($container));
$app->add(new DevCommand($container));
$app->add(DoneCommand::create($container));
$app->add(FeatureCommand::create($container));
$app->add(new HookLibCommand());
$app->add(HotfixCommand::create($container));
$app->add(new MajorCommand($container));
$app->add(new MinorCommand($container));
$app->add(new PatchCommand($container));
$app->add(ReleaseCommand::create($container));
$app->add(new UnBuildCommand($container));
$app->add(new UpgradeCommand($container));
$app->add(new VersionCommand($container));
$app->run();
