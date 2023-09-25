<?php

namespace AKlump\WebPackage\Hooks;

use AKlump\LocalTimezone\LocalTimezone;
use AKlump\WebPackage\Config\Config;
use AKlump\WebPackage\Config\LoadConfig;
use AKlump\WebPackage\Helpers\GetRootPath;
use Symfony\Component\Filesystem\Path;
use Symfony\Contracts\EventDispatcher\Event;

class HookEvent extends Event {

  private $version;

  private $previousVersion;

  private $packageName;

  private $description;

  private $homepage;

  private $author;

  public function setPackageName($packageName): self {
    $this->packageName = $packageName;

    return $this;
  }

  public function setDescription($description): self {
    $this->description = $description;

    return $this;
  }

  public function setHomepage($homepage): self {
    $this->homepage = $homepage;

    return $this;
  }

  public function setAuthor($author): self {
    $this->author = $author;

    return $this;
  }

  public function setVersion(string $version): self {
    $this->version = $version;

    return $this;
  }

  public function setPreviousVersion(string $version): self {
    $this->previousVersion = $version;

    return $this;
  }

  public function getVersion(): string {
    return $this->version ?? '';
  }

  public function getPreviousVersion(): string {
    return $this->previousVersion ?? '';
  }

  public function getPackageName(): string {
    return $this->packageName ?? '';
  }

  public function getDescription(): string {
    return $this->description ?? '';
  }

  public function getHomepage(): string {
    return $this->homepage ?? '';
  }

  public function getWebPackageRoot(): string {
    return WEB_PACKAGE_ROOT;
  }

  public function getHook(): string {
    return $this->hookPath ?? '';
  }

  public function setHook(string $hook): string {
    return $this->hookPath = $hook;
  }

  public function getAuthor(): string {
    return $this->author ?? '';
  }

  public function getRoot(): string {
    return ROOT_PATH;
  }

  public function getDateTime(): string {
    return date_create('now', LocalTimezone::get())->format('D M m H:i:s T Y');
  }

  public function getInfoFile(): string {
    $info_file = (new LoadConfig())()[Config::VERSION_FILE];
    if (!Path::isAbsolute($info_file)) {
      $info_file = Path::makeAbsolute($info_file, ROOT_PATH);
    }

    return $info_file;
  }
}
