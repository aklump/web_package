#!/bin/bash
#
# @file
# This will use grab to get the file copies (not symlinks)
#
grab=$(type grab >/dev/null 2>&1 && which grab)
if [ "$grab" ]; then

  # Go through and grab any symlinks in the custom folder
  cd "$7/web/modules/custom"
  for i in $(ls); do
    ! test -L $i || grab -f $i --lang=d8
  done

  cd "$7/web/modules/contrib"
  ! test -L users_export || grab -f users_export --lang=d8
  ! test -L loft_data_grids || grab -f loft_data_grids --lang=d8
  cd "$7/lib"
  ! test -L loft_php_lib || grab -f loft_php_lib
  ! test -L lobster || grab -f lobster
fi
