#!/usr/bin/env bash

#
# @file Run `bump build` in all custom child extensions (custom module, custom theme, etc.)
#

# List all extensions that use web_package to build here.
declare -a children=("./web/modules/custom/my_module" "./web/themes/custom/my_theme")

bump=$(type web_package >/dev/null 2>&1 && which web_package)
[ "$bump" ] || exit 255
echo "BUILDING CHILDREN"
for path in ${children[@]} ; do
  echo "├── $(basename $path)"
  if [ -d "$path/.web_package" ]; then
    (cd "$path" && $bump build) || exit 1
  fi
done

## Do anything else custom here, e.g. `./bin/build.css.sh`
#(cd "./web/themes/custom/my_other_theme" && ./bin/build_css.sh) || exit 1
