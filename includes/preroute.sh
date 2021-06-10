#!/bin/bash
#
# @file
# Pre-route is automatically called every time example_app.sh is executed, after bootstrap and before the route is executed.  The route is available as $lobster_route

# Check for updates if needed
if [ "$lobster_op" != 'update' ] && is_initialized >/dev/null && needs_update; then
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
  wp_git_root="$(cd -P "$(dirname "$wp_git_root")" && pwd)"
  lobster_echo
  lobster_warning "Configuration says the git root should be here: \"$wp_git_root/.git\", however it does not exist."
  lobster_echo
  lobster_warning "- Do you need to run \"cd $wp_git_root/ && git init\"?"
  lobster_warning "- Is the path in the config (git_root) incorrect?"
  lobster_warning "- If you're not using Git, please remove (git_root) from your config file."
  lobster_echo
fi

source="${BASH_SOURCE[0]}"
while [ -h "$source" ]; do # resolve $source until the file is no longer a symlink
  dir="$(cd -P "$(dirname "$source")" && pwd)"
  source="$(readlink "$source")"
  [[ $source != /* ]] && source="$dir/$source" # if $source was a relative symlink, we need to resolve it relative to the path where the symlink file was located
done
root="$(cd -P "$(dirname "$source")" && pwd)"
