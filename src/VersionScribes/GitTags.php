<?php

namespace AKlump\WebPackage\VersionScribes;

use AKlump\WebPackage\Traits\ReaderTrait;
use AKlump\WebPackage\VersionScribeInterface;
use z4kn4fein\SemVer\Version;

/**
 * Read the version from the list of git tags.
 * 
 * // TODO This is not yet ready for use.
 *
 * @code
 * info_file = ".git"
 * @endcode
 */
class GitTags implements VersionScribeInterface {

  use ReaderTrait;

  private $source;

  public function __construct(string $source) {
    $this->source = $source;
  }

  public function read(): Version {
    exec(sprintf("cd %s && git tag 2> /dev/null", $this->source), $tags);
    usort($tags, 'version_compare');
    while (($version = (string) array_pop($tags))) {
      try {
        $version = Version::parse($version, FALSE);

        return $version;
      }
      catch (\Exception $exception) {
        // Purposefully left blank.
      }
    }

    return $this->getVersion('');
  }

  /**
   * @inheritDoc
   */
  public function write(Version $version): bool {
    // TODO I'm not sure how to handle this right now.  Need to think.
    return TRUE;
  }
}
