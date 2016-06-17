#!/bin/bash
# 
# @file
# Initialize the current directory by an app configuration file in $PWD
lobster_include "pre_init"

if [ -f "./$lobster_app_config" ]; then
  lobster_failed "$PWD is already initialized."
fi

if [ "$LOBSTER_PWD_ROOT" ] && [ "$LOBSTER_PWD_ROOT" != "$HOME" ]; then
  lobster_warning "You are currently in a subdirectory of an initialized directory (root is $LOBSTER_PWD_ROOT)."
  if ! lobster_confirm "Are you sure?"; then
    lobster_failed
  fi
fi

# Make sure the config directory, if specified exists.
#[ -d $(dirname "$lobster_app_config") ] || mkdir -p $(dirname "$lobster_app_config")

if ! test -e "$LOBSTER_APP_ROOT/install/$lobster_app_config"; then
  lobster_failed "Missing template configuration /install/$lobster_app_config"
fi

cp "$LOBSTER_APP_ROOT/install/$lobster_app_config" "./$lobster_app_config" && lobster_success "Your app has been initialized."

lobster_include "post_init"
