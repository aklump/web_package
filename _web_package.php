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
use AKlump\WebPackage\Config\ConfigDefaults;
use AKlump\WebPackage\Config\LoadConfig;
use AKlump\WebPackage\Git\GitProxy;
use AKlump\WebPackage\Helpers\GetRootPath;
use AKlump\WebPackage\Model\Context;
use AKlump\WebPackage\Upgrade\ComposerJson;
use AKlump\WebPackage\VersionScribeFactory;
use League\Container\Argument\LiteralArgument;
use League\Container\Container;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Filesystem\Filesystem;

// https://getcomposer.org/doc/articles/vendor-binaries.md#finding-the-composer-autoloader-from-a-binary
if (isset($GLOBALS['_composer_autoload_path'])) {
  // As of Composer 2.2...
  $_composer_autoload_path = $GLOBALS['_composer_autoload_path'];
}
else {
  // < Composer 2.2
  $ds = DIRECTORY_SEPARATOR;
  foreach ([
               __DIR__ . $ds . '..' . $ds . '..' . $ds . 'autoload.php',
               __DIR__ . $ds . '..' . $ds . 'vendor' . $ds . 'autoload.php',
               __DIR__ . $ds . 'vendor' . $ds . 'autoload.php',
           ] as $_composer_autoload_path) {
    if (file_exists($_composer_autoload_path)) {
      break;
    }
  }
}
$class_loader = require_once $_composer_autoload_path;

$filesystem = new Filesystem();

// This is the path where the install directory (.web_package) is located, or
// will be created.  It is handed to everything that needs it through the
// container, not through a global.
$root_path = (new GetRootPath())(getcwd());

// Automatic Composer autoloader support.
$user_autoload = $root_path . '/' . ConfigDefaults::INSTALL_DIR . '/vendor/autoload.php';
if (file_exists($user_autoload)) {
  require_once $user_autoload;
}

// Autowire PSR-4 namespace.
$user_src_directory = $root_path . '/' . ConfigDefaults::INSTALL_DIR . '/src';
$user_composer_json = $root_path . '/' . ConfigDefaults::INSTALL_DIR . '/composer.json';
try {
  if (file_exists($user_src_directory) && !file_exists($user_composer_json)) {
    (new ComposerJson())->execute($user_composer_json);
  }
}
catch (\Exception $exception) {
  die($exception->getMessage() . PHP_EOL);
}

// TODO Remove this before version 4.0
if (file_exists($user_src_directory)) {
  $class_loader->addPsr4('AKlump\WebPackage\User\\', [
      $user_src_directory,
  ]);
  // This is legacy support.
  $class_loader->addPsr4('AKlump\WebPackage\\', [
      $user_src_directory,
  ]);
}

$app = new Application();
$app->setName('web_package');
$app->setVersion('3.3.2');

// Application::add() was replaced by addCommand() in symfony/console 7.4 and removed in 8.0.
$add_command = function (Command $command) use ($app) {
  if (method_exists($app, 'addCommand')) {
    $app->addCommand($command);
  }
  else {
    $app->add($command);
  }
};

$container = new Container();
$container->add('root_path', new LiteralArgument($root_path));
$container->add('config.loader', LoadConfig::class)
    ->addArgument('root_path');
$container->add('context', Context::class)
    ->addArguments(['config.loader', 'root_path']);
$container->add('scribe.factory', VersionScribeFactory::class)
    ->addArguments(['config.loader', 'context']);
$container->add('git', GitProxy::class);

$add_command(new InitCommand(
    $container,
    ConfigDefaults::INSTALL_DIR,
    __DIR__ . '/install/template/',
    $filesystem
));

// TODO Remove all create() methods for __construct().
$add_command(new BuildCommand($container));
$add_command(new PublishCommand($container));
$add_command(new ConfigCommand($container));
$add_command(new DevCommand($container));
$add_command(DoneCommand::create($container));
$add_command(FeatureCommand::create($container));
$add_command(new HookLibCommand());
$add_command(HotfixCommand::create($container));
$add_command(new MajorCommand($container));
$add_command(new MinorCommand($container));
$add_command(new PatchCommand($container));
$add_command(ReleaseCommand::create($container));
$add_command(new UnBuildCommand($container));
$add_command(new UpgradeCommand($container));
$add_command(new VersionCommand($container));
$app->run();
