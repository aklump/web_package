#!/usr/bin/env bash
#
# @file Do a composer update
#

composer=$(type composer >/dev/null 2>&1 && which composer)
cd "$7" && $composer update
