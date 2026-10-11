<?php

namespace AKlump\WebPackage\Helpers;

/**
 * Get the directory where web_package itself is installed.
 *
 * This is NOT the root of the project being managed, see GetRootPath for that.
 */
class GetWebPackageRoot {

  /**
   * @return string
   *   The absolute path to the web_package application, i.e., the directory
   *   that contains _web_package.php, includes/ and hook_examples/.
   */
  public function __invoke(): string {
    return dirname(__DIR__, 2);
  }

}
