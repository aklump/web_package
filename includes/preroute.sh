#!/bin/bash
#
# @file
# Pre-route is automatically called every time example_app.sh is executed, after bootstrap and before the route is executed.  The route is available as $lobster_route

# Check for updates if needed
if [ "$lobster_op" != 'update' ] && is_initialized > /dev/null && needs_update; then
  lobster_failed "Updated required; use 'bump update'"
fi

# Expand info v to info version
if [ "$lobster_op" == 'info' ]; then
  if [ "${lobster_args[1]}" == 'v' ]; then
    lobster_args[1]='version'
  fi
  if [ "${lobster_args[1]}" == 'i' ]; then
    lobster_args[1]=${lobster_args[2]}
  fi
fi

if [ "$wp_git_root" ] && ! test -e $wp_git_root/.git; then
  lobster_warning "Git root $wp_git_root/.git does not exist; check your config file for git_root"
fi
