#!/bin/bash
#
# @file
# This will use grab to get the file copies (not symlinks)
#
grab=$(type grab >/dev/null 2>&1 && which grab)
if [ "$grab" ]; then
  cd ./lib
  grab -f loft_php_lib
#  grab -f lobster
fi
