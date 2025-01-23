<?php

namespace AKlump\Knowledge\User;

class GetGitIgnoreContents {

  public function __invoke(string $app_root): string {
    return file_get_contents($app_root . '/install/template/gitignore');
  }
}
