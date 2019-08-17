#!/usr/bin/env bash
#
# @file
# Applies the build process to child projects.
#
bump=$(type web_package >/dev/null 2>&1 && which web_package)
if [ "$bump" ]; then
  echo "building children"
  (cd $7/web/sites/all/modules/custom/gop3_core && $bump build) || build_fail_exception
  (cd $7/web/sites/all/modules/custom/gop3_media && $bump build) || build_fail_exception
  (cd $7/web/sites/all/themes/gop5_theme && $bump build) || build_fail_exception
fi
