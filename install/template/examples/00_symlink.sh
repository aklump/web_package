#!/bin/bash
# 
# @file
# This will use grab to get the file copies (not symlinks)
# 
grab=$(type grab >/dev/null 2>&1 && which grab)
if [ "$grab" ]; then
  cd "$7/web/modules/contrib"
  grab -f -s users_export --lang=d8
  grab -f -s loft_data_grids --lang=d8
  cd "$7/lib"
  grab -f -s loft_php_lib
  grab -f -s lobster
fi
