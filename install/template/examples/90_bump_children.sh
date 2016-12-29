#!/usr/bin/env bash
#
# @file
# Applies the build proces to child projects
#
bump=$(type bump >/dev/null 2>&1 && which bump)
if [ "$bump" ]; then
  (cd $7/web/sites/all/themes/gop5_theme && $bump build)
  (cd $7/web/sites/all/modules/custom/gop3_core && $bump build)
  (cd $7/web/sites/all/themes/gop5_theme/guides && $bump build)
fi
