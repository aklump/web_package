#!/bin/bash
# 
# @file
# Bootstrap is automatically called every time example_app.sh is executed, but after Lobster's bootstrap is called.

#
# Determine the root folder of the project; where .web_package lives
#
project_root=${PWD}

# @todo This configuration needs to be merged with Lobster's app way.
source $LOBSTER_APP_ROOT/includes/functions.sh
#if is_initialized >/dev/null; then
  load_config
#fi

# Detect if 'info' is implied as the operation
declare -a array=("${lobster_args[0]}" "name" "version" "v" "i")
if lobster_in_array ${array[@]}; then
  lobster_args=("info" "${lobster_args[@]}")
fi

# Detect if 'bump' is implied as the operation
declare -a array=("${lobster_args[0]}" "major" "minor" "patch" "alpha" "beta" "rc" "hotfix" "release")
if lobster_in_array ${array[@]}; then
  lobster_args=("bump" "${lobster_args[@]}")
fi
