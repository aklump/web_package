#!/usr/bin/env bash

lobster_echo
dir=$HOME/.web_package/
if [ "${lobster_args[1]}" ]; then
  path="$dir/${lobster_args[1]}"
  if ! test -e $path; then
    path="$dir/config_${lobster_args[1]}"
  fi
  cat $path;
else
  lobster_success "Available templates at $path:"
  ls $dir
fi
