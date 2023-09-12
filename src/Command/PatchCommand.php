<?php

namespace AKlump\WebPackage\Command;

use AKlump\WebPackage\Access\IsInitialized;
use AKlump\WebPackage\Config\GetVersionScribe;
use AKlump\WebPackage\Config\LoadConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AKlump\WebPackage\Model\Version;

class PatchCommand extends Command {

  protected static $defaultName = 'patch';

  protected function configure() {
    $this
      ->setDescription("Increment version one patch step without hooks.");
  }

  protected function execute(InputInterface $input, OutputInterface $output) {

    //
    // Access Checking
    //
    if (!(new IsInitialized())->access()) {
      throw new \RuntimeException("You must initialize this directory first.");
    }

    $config = (new LoadConfig())();
    $scribe = (new GetVersionScribe($config))();

    try {
      $version = $scribe->read();
      $version = Version::parse($version, FALSE);
      $next_version = $this->getNextVersion($version);
    }
    catch (\Exception $exception) {
      // TODO Handle something like 8.x-1.3
    }

    //
    // Finalize new version
    //
    $scribe->write($next_version);

    $output->writeln(sprintf('<info>Version bumped from %s to %s</info>', $version, $next_version));

    return Command::SUCCESS;
  }

  protected function getNextVersion(Version $version): Version {
    return $version->getNextPatchVersion();
  }

}
