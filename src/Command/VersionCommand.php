<?php

namespace AKlump\WebPackage\Command;

use AKlump\WebPackage\Access\IsInitialized;
use AKlump\WebPackage\Config\GetVersionScribe;
use AKlump\WebPackage\Config\LoadConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class VersionCommand extends Command {

  protected static $defaultName = 'version';

  protected function configure() {
    $this
      ->setAliases(['v'])
      ->setDescription("Print out the current version.");
  }


  protected function execute(InputInterface $input, OutputInterface $output) {
    $config = (new LoadConfig())();
    $output->writeln((string) (new GetVersionScribe($config))()->read());

    return Command::SUCCESS;
  }

}
