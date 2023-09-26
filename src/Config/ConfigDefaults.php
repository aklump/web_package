<?php

namespace AKlump\WebPackage\Config;

class ConfigDefaults {

  /**
   * @var string The default main branch name.
   */
  const MAIN_BRANCH = 'master';

  /**
   * @var string The default development branch name.
   */
  const DEVELOP_BRANCH = '';

  const DO_VERSION_COMMIT = TRUE;

  const PATCH_PREFIX = '.';

  const PRESERVE_PATCH_ZERO = TRUE;

  const INITIAL_VERSION = '0.0.0';

  const CREATE_TAGS = 'patch';

  const PUSH_MASTER = TRUE;

  const PUSH_DEVELOP = TRUE;

  const PUSH_TAGS = TRUE;

  /**
   * Path is relative to directory where .web_package is.
   */
  const VERSION_FILE = '.web_package/config.yml';

}
