#!/bin/bash
# 
# @file
# Copy distribution files to /dist
# 
cp "$7/jquery.photo_essay.js" "$7/dist/"
cp "$7/photo_essay.css" "$7/dist/"
mkdir -p "$7/dist/images"
cp "$7/images/loading.gif" "$7/dist/images/"
cp "$7/images/loading-sm.gif" "$7/dist/images/"
