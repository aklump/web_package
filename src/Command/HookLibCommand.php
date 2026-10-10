<?php

namespace AKlump\WebPackage\Command;

use AKlump\WebPackage\Helpers\GetWebPackageRoot;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class HookLibCommand extends Command {

  protected static $defaultName = 'hooklib';

  public function __construct() {
    parent::__construct(static::$defaultName);
  }

  protected function configure(): void {
    $this->setDescription("Display hook example code information.");
  }

  protected function execute(InputInterface $input, OutputInterface $output): int {
    $output->writeln('<info>A number of hook examples can be found at:</info>');
    $output->writeln((new GetWebPackageRoot())() . '/hook_examples/');

    return Command::SUCCESS;
  }

}
