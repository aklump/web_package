#!/usr/bin/env bash

#
# This file should be used when you are committing the vendor directory.
#

composer install --no-dev && composer dumpautoload --optimize && git add composer.lock && git add vendor || build_fail_exception

# This is for a specific version of PHP.
#/Applications/MAMP/bin/php/php5.6.32/bin/php  /Users/aklump/bin/composer install --no-dev && composer dumpautoload --optimize && git add composer.lock && git add vendor || build_fail_exception
