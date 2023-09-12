<?php

namespace AKlump\WebPackage\Command;

use AKlump\WebPackage\Access\IsInitialized;
use AKlump\WebPackage\Config\GetVersionScribe;
use AKlump\WebPackage\Config\LoadConfig;
use AKlump\WebPackage\Hooks\HookManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use z4kn4fein\SemVer\Version;

class PatchCommand extends Command {

  protected static $defaultName = 'patch';

  protected function execute(InputInterface $input, OutputInterface $output) {

    //
    // Access Checking
    //
    if (!(new IsInitialized(getcwd()))) {
      throw new \RuntimeException("You must initialize this directory first.");
    }

    $scribe = (new GetVersionScribe())();
    $version = Version::parse($scribe->read());
    $next_version = $this->getNextVersion($version);

    //
    // Hook handling.
    //
    $hook_args = [
      'previous' => (string) $version,
      'version' => (string) $next_version,
    ];
    //    (new HookManager())->run($hook_args);

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
