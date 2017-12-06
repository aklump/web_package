#!/usr/bin/env bash
#
# @file
# Remove generated files before all other build steps
#

web_package=$(type web_package >/dev/null 2>&1 && which web_package)
cd $7 && web_package unbuild
