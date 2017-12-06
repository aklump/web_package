#!/usr/bin/env bash
#
# @file
# Applies the build process to child projects
#
web_package=$(type web_package >/dev/null 2>&1 && which web_package)
if [ "$web_package" ]; then
  (cd $7/web/sites/all/themes/gop5_theme && $web_package build)
  (cd $7/web/sites/all/modules/custom/gop3_core && $web_package build)
  (cd $7/web/sites/all/themes/gop5_theme/guides && $web_package build)
fi
