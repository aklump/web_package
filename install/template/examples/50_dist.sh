#!/bin/bash
# 
# @file
# Copy distribution files to /dist
#

# Allow time for all CodeKit to compile.
sleep 3

# First, wipe out the dist folder for a clean slate.
(cd "$7" && (test -d dist && rm -r dist) && mkdir dist)

# Now copy of the necessary folders; don't check first because we want a loud failure.
rsync -a "$7/sass/" "$7/dist/sass/"
rsync -a "$7/src/" "$7/dist/src/"
rsync -a "$7/vendor/" "$7/dist/vendor/"
cp "$7/composer.json" "$7/dist/"

# ... and files.
cp "$7/README.md" "$7/dist/"
cp "$7/CHANGELOG.md" "$7/dist/"
cp "$7/LoftImages.js" "$7/dist/"
cp "$7/LoftImages.min.js" "$7/dist/"
