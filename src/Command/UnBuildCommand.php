<?php

namespace AKlump\WebPackage\Command;

use AKlump\WebPackage\Config\LoadConfig;
use AKlump\WebPackage\Helpers\GetHookEvent;
use AKlump\WebPackage\Helpers\GetInfo;
use AKlump\WebPackage\Hooks\HookEvent;
use AKlump\WebPackage\Hooks\HookManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AKlump\WebPackage\Config\GetVersionScribe;

class UnBuildCommand extends Command {

  protected static $defaultName = 'unbuild';

  protected function configure() {
    $this
      ->setDescription('Run unbuild-type hooks without affecting versioning.')
      ->addArgument('filter', InputArgument::OPTIONAL, 'Limit which hooks run. Maybe be substring or glob pattern.')
      ->setHelp("The filter works as a substring, so given these unbuild hooks:\n\nipsum.php\nlorem.php\nlorem.sh\n\nHere are filter examples:\n\nbump unbuild lor -> (lorem.php, lorem.sh)\nbump unbuild .php -> (ipsum.php, lorem.php)\n\nHooks are executed in alphabetical order. To control order use double-digit prefixes:\n\n00_lorem.sh\n00_lorem.php\n10_ipsum.php");
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $config = (new LoadConfig())();
    $event = (new GetHookEvent($config))();
    $scribe = (new GetVersionScribe($config))();
    $event->setPreviousVersion($scribe->read());
    $event->setVersion($scribe->read());
    $filter = $input->getArgument('filter') ?? '';
    (new HookManager($output, $event))->run('unbuild', $filter);

    return Command::SUCCESS;
  }

}
