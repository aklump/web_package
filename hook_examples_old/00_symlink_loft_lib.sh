#!/usr/bin/env bash
#
# Create a symlink to the local loft-lib package
#
[ -d "$7/vendor/aklump/" ] || mkdir -p "$7/vendor/aklump/"
[ -e "$7/vendor/aklump/loft-lib" ] && rm -r "$7/vendor/aklump/loft-lib"
cd "$7/vendor/aklump/" && ln -s /Users/aklump/Code/Packages/php/loft_lib/dist loft-lib && exit 0
exit 1
