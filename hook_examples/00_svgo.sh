#!/usr/bin/env bash
# A step to optimize all svg images
svgo=$(type svgo >/dev/null 2>&1 && which svgo)

# svgo doesn't descend into child directories, so list out each directory as appropriate.
$svgo "$7" --multipass
$svgo "$7/images" --multipass
