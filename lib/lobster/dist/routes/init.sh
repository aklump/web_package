#!/bin/bash
# 
# @file
# Initialize the current directory by an app configuration file in $LOBSTER_CWD.
lobster_include "pre_init"

if [ -f "./$lobster_app_config" ]; then
  lobster_failed "$PWD is already initialized."
elif [ "$LOBSTER_CWD_ROOT" ] && [ "$LOBSTER_CWD_ROOT" != "$HOME" ]; then
  lobster_warning "You are currently in a subdirectory of an initialized directory (root is $LOBSTER_CWD_ROOT)."
  if ! lobster_confirm "Are you sure?"; then
    lobster_failed
  fi
fi

## If our app wants a directory then...
base="$LOBSTER_APP_ROOT/install";
success="Your app has been initialized."
fail="Missing installation configuration template: "
if [ "$lobster_app_config_dir" ]; then
  test -d "$base/$lobster_app_config_dir/" || lobster_fail "$fail$base/$lobster_app_config"
  rsync -a "$base/$lobster_app_config_dir/" "./$lobster_app_config_dir/" && lobster_success "$success"
else
  test -f "$base/$lobster_app_config" || lobster_fail "$fail$base/$lobster_app_config"
  cp "$base/$lobster_app_config" "./$lobster_app_config" && lobster_success "$success"
fi

lobster_include "post_init"
