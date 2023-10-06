#!/usr/bin/env bash
declare -a children=("./web/modules/custom/gop3_core" "./web/themes/custom/gop_theme")
bump=$(type web_package >/dev/null 2>&1 && which web_package)
[ "$bump" ] || exit 255
echo "BUILDING CHILDREN"
for path in ${children[@]} ; do
  echo "├── $(basename $path)"
  (cd "$path" && $bump build) || exit 1
done
