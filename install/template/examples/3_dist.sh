#!/bin/bash
# 
# @file
# Copy distribution files to /dist
# 
sleep 3
test -d "$7/dist" || mkdir -p "$7/dist"
cp "$7/LoftImages.js" "$7/dist/"
cp "$7/LoftImages.min.js" "$7/dist/"
rsync -av "$7/sass/" "$7/dist/sass/"
