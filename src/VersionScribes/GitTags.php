<?php

namespace AKlump\WebPackage\VersionScribes;

use AKlump\WebPackage\VersionScribeInterface;
use AKlump\WebPackage\Model\Version;

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

  public function getFilepath(): string {
    return '';
  }

  public function read(): string {
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

    return VersionScribeInterface::DEFAULT;
  }

  /**
   * @inheritDoc
   */
  public function write(string $version): bool {
    // TODO I'm not sure how to handle this right now.  Need to think.
    return TRUE;
  }
}
