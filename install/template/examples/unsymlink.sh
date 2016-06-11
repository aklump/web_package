#!/bin/bash
# 
# @file
# This will use grab to get the file copies (not symlinks)
# 
if test -e ~/bin/grab; then
  (cd ./bower_components && grab -f loft_images loft-images)
  (cd ./js && grab -f loft_toggler)
  (cd ./sass && grab -f loft_compass)
fi