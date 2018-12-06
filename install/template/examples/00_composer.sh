#!/usr/bin/env bash

#
# Handles composer update, optimize, and git add .lock
#

composer update
composer dumpautoload --optimize
git add composer.lock
