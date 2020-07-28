#!/bin/bash
#
# @file
# This will use grab to get the files as symlinks.
#

cd "$7/js" && (! test -e taskcamp_reporter.min.js || rm taskcamp_reporter.min.js) && ln -sv /Users/aklump/Code/Projects/taskcamp/site-reporter/web/dist/taskcamp_reporter.min.js taskcamp_reporter.min.js

grab=$(type grab >/dev/null 2>&1 && which grab)
if [ "$grab" ]; then
  cd "$7/web/modules/contrib"
  grab -f -s users_export --lang=d8
  grab -f -s loft_data_grids --lang=d8
  cd "$7/lib"
  grab -f -s loft_php_lib
  grab -f -s lobster
fi
