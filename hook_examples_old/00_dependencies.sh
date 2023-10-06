#!/usr/bin/env bash

#
# @file Update dependencies using yarn
#


cd "$7/js" && (! test -e taskcamp_reporter.min.js || rm taskcamp_reporter.min.js) && ln -sv /Users/aklump/Code/Projects/taskcamp/site-reporter/web/dist/taskcamp_reporter.min.js taskcamp_reporter.min.js

yarn || hook_exception
composer update || hook_exception

