#!/usr/bin/env bash
bump=$(type web_package >/dev/null 2>&1 && which web_package)
[ "$bump" ] || exit 255
echo "BUILDING COMPONENTS"
for name in $(ls components) ; do
  ! [[ -d "components/$name/.web_package/hooks/build" ]] && continue
  echo "├── components/$name/"
  (cd "components/$name" && $bump build) || exit 1
done
