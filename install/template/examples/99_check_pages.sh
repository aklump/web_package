#!/bin/bash

cd $7
./vendor/bin/check_pages runner.php --dir=$7/tests_check_pages || build_fail_exception "Some page checks failed."
