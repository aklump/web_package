#!/usr/bin/env bash
#
# @file Do a composer update
#
# You should include composer1.sh as well so the note prints before the delay begins.
composer=$(type composer >/dev/null 2>&1 && which composer)
cd "$7" && $composer update
