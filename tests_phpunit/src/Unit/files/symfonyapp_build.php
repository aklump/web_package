#!/usr/bin/env php
<?php

use AKlump\PhpSwap\Command\ExecuteCommand;
use AKlump\PhpSwap\Command\ListCommand;
use Symfony\Component\Console\Application;

require __DIR__ . '/vendor/autoload.php';

$app = new Application();
$app->setName('phpswap');
$app->setVersion('1.2.3+4');
$app->add(new ListCommand());
$app->add(new ExecuteCommand());
$app->run();
