#!/usr/bin/env bash

#
# Handles composer update, optimize, and git add .lock
#

## Often times it is not a good idea to run composer update, especially on libraries because the host PHP is not yet known.

#/Applications/MAMP/bin/php/php5.6.32/bin/php  /Users/aklump/bin/composer ...
composer update && composer dumpautoload --optimize && git add composer.lock || build_fail_exception

