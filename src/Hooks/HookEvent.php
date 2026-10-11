<?php

namespace AKlump\WebPackage\Hooks;

use AKlump\LocalTimezone\LocalTimezone;
use AKlump\WebPackage\Helpers\GetWebPackageRoot;
use Symfony\Contracts\EventDispatcher\Event;

class HookEvent extends Event {

  private $version;

  private $previousVersion;

  private $packageName;

  private $description;

  private $homepage;

  private $author;

  /**
   * @var string
   */
  private $hookPath;

  /**
   * @var string
   */
  private $root;

  /**
   * @var string
   */
  private $infoFile;

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
    return (new GetWebPackageRoot())();
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

  /**
   * @param string $root
   *   The absolute path of the project, i.e., the directory containing
   *   .web_package.
   */
  public function setRoot(string $root): self {
    $this->root = $root;

    return $this;
  }

  public function getRoot(): string {
    return $this->root ?? '';
  }

  public function getDateTime(): string {
    return date_create('now', LocalTimezone::get())->format('D M m H:i:s T Y');
  }

  /**
   * @param string $info_file
   *   The absolute path to the file that holds the package info, which is also
   *   the version file.
   */
  public function setInfoFile(string $info_file): self {
    $this->infoFile = $info_file;

    return $this;
  }

  public function getInfoFile(): string {
    return $this->infoFile ?? '';
  }
}
