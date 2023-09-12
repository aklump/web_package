<?php

namespace AKlump\WebPackage\Helpers;

class GetHooksDirectory {

  /**
   * @param string $hook_type
   *
   * @return string
   */
  public function __invoke(string $hook_type): string {
    if (empty($hook_type)) {
      throw new \InvalidArgumentException('$hook_type may not be empty');
    }
    $root_path = (new GetRootPath())();

    return $root_path . "/.web_package/hooks/$hook_type/";
  }

}
