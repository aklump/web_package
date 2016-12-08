#!/bin/bash
# 
# @file
# Copy distribution files to /dist
# 
sleep 3
test -d "$7/dist" || mkdir -p "$7/dist"
test -d "$7/sass/" && rsync -av "$7/sass/" "$7/dist/sass/"
test -d "$7/src" && rsync -a "$7/src/" "$7/dist/src/"
test -d "$7/vendor" && rsync -a "$7/vendor/" "$7/dist/vendor/"
test -f "$7/composer.json" && cp "$7/composer.json" "$7/dist/composer.json"

cp "$7/LoftImages.js" "$7/dist/"
cp "$7/LoftImages.min.js" "$7/dist/"
