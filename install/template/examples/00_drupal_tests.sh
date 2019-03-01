#!/usr/bin/env bash

export TEST_BASE_URL=http://mysite.loft/
phpunit --configuration $7/../tests/phpunit.xml || build_fail_exception "Tests did not pass!"
echo "All automated Drupal Tests have passed."
