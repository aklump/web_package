#!/bin/bash
# 
# @file
# This will use grab to get the file copies (not symlinks)
# 
if test -e ~/bin/grab; then
  (cd ./lib && grab -f loft_php_lib)
  (cd ./lib && grab -f lobster)
fi