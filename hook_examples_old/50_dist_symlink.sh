#!/bin/bash
# 
# @file
# Copy distribution files to /dist
#

# First, wipe out the dist folder then symlink it to the top-level
cd "$7" && (! test -e dist || rm -r dist) && ln -s . dist
