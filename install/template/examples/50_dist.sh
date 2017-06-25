#!/bin/bash
# 
# @file
# Copy distribution files to /dist
#
# YOU MUST USE 00_clean_slate.sh before this
#

# Allow time for all CodeKit to minify.
while [ ! -f "$7/LoftImages.min.js" ]; do
  sleep 1
done

test -h "$7/dist" && rm "$7/dist"
test -d "$7/dist" || mkdir -p "$7/dist"

# Now copy of the necessary folders; don't check first because we want a loud failure.
rsync -a "$7/sass/" "$7/dist/sass/"
rsync -a "$7/src/" "$7/dist/src/"
rsync -a "$7/vendor/" "$7/dist/vendor/"
cp "$7/composer.json" "$7/dist/"

# ... and files.
test -e "$7/README.md" && cp "$7/README.md" "$7/dist/"
test -e "$7/CHANGELOG.md" && cp "$7/CHANGELOG.md" "$7/dist/"
cp "$7/LoftImages.js" "$7/dist/"
cp "$7/LoftImages.min.js" "$7/dist/"
