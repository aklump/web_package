#!/usr/bin/env bash
#
#
# Process svgo on image directories
#
# @see https://github.com/svg/svgo
# @see https://github.com/svg/svgo/issues/300 re: multipass
#
declare -a image_dirs=("${7}/images");
svgo=/usr/local/bin/svgo

for dir in "$image_dirs"; do
  cd "$dir" && pwd && $svgo . --multipass
done
