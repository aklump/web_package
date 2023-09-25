<?php

namespace AKlump\WebPackage;

use AKlump\WebPackage\Config\Config;
use AKlump\WebPackage\Config\LoadConfig;
use AKlump\WebPackage\Traits\HasConfigTrait;
use AKlump\WebPackage\VersionScribes\DrupalInfo;
use AKlump\WebPackage\VersionScribes\IniFile;
use AKlump\WebPackage\VersionScribes\Json;
use AKlump\WebPackage\VersionScribes\SymfonyConsoleApplication;
use AKlump\WebPackage\VersionScribes\Text;
use AKlump\WebPackage\VersionScribes\Yaml;
use AKlump\WebPackage\Model\Version;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;

class VersionScribeFactory {

  use HasConfigTrait;

  /**
   * @var \Symfony\Component\Filesystem\Filesystem
   */
  private $filesystem;

  public function __construct(LoadConfig $loader) {
    $this->setConfig($loader());
    // TODO DI?
    $this->filesystem = new Filesystem();
  }

  /**
   * @return \AKlump\WebPackage\VersionScribeInterface|null
   *
   * @throws \InvalidArgumentException If the file does not exist.
   */
  public function __invoke(): ?VersionScribeInterface {
    $filepath = $this->getConfig()[Config::VERSION_FILE];
    if (!$this->filesystem->exists($filepath)) {
      return NULL;
    }
    $basename = strtolower(basename($filepath));
    $extension = Path::getExtension($basename);
    if ('json' === $extension) {
      return new Json($filepath);
    }
    elseif ('info' === $extension) {
      return new DrupalInfo($filepath);
    }
    elseif ('ini' === $extension
      // The legacy file .web_package/config can be used and it's .ini.
      || ('' === $extension && 'config' === $basename)) {
      return new IniFile($filepath);
    }
    //    elseif ('.git' === $basename) {
    //      return new GitTags($filepath);
    //    }
    elseif (in_array($extension, ['yml', 'yaml'])) {
      return new Yaml($filepath);
    }
    elseif ('php' === $extension) {
      // TODO This is only available in version >=6
      //      $contents = $this->filesystem->read($filepath);
      $contents = file_get_contents($filepath);
      if (preg_match("/\->setVersion\(['\"\d\.]+?\)/", $contents)) {
        return new SymfonyConsoleApplication($filepath);
      }
    }
    else {
      // Let's just see if we can find a version in the file contents somewhere.
      // This case will allow us to use a simple text file with the version
      // string in it by itself.
      $contents = file_get_contents($filepath);
      $version = Version::parse($contents, FALSE);
      if (substr_count($contents, (string) $version) === 1) {
        return new Text($filepath);
      }
    }

    return NULL;
  }

  private function tryCreateMissingVersionFile(string $filepath) {
    $this->filesystem->touch($filepath);
    if (!file_exists($filepath)) {
      throw new \RuntimeException(sprintf('Failed to create: %s', $filepath));
    }
  }
}
