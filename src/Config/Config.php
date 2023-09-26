<?php

namespace AKlump\WebPackage\Config;

class Config {

  /**
   * @var string The config key for the main branch, not the branch name.
   */
  const MAIN_BRANCH = 'master';

  /**
   * @var string The config key for the development branch, not the branch name.
   */
  const DEVELOP_BRANCH = 'develop';

  const DO_VERSION_COMMIT = 'do_version_commit';

  const PATCH_PREFIX = 'patch_prefix';

  const PRESERVE_PATCH_ZERO = 'preserve_patch_zero';

  const CREATE_TAGS = 'create_tags';

  const PUSH_MASTER = 'push_master';

  const INITIAL_VERSION = 'init_version';

  const PUSH_DEVELOP = 'push_develop';

  const PUSH_TAGS = 'push_tags"';

  const VERSION_FILE = 'version_file';


}
