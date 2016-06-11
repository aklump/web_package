#!/bin/bash
# 
# @file
# Initialize the current directory by an app configuration file in $PWD

if [ -f "$lobster_app_config" ]; then
  lobster_failed "$PWD is already initialized."
fi

if [ "$LOBSTER_PWD_ROOT" ] && [ "$LOBSTER_PWD_ROOT" != "$HOME" ]; then
  lobster_warning "You are currently in a subdirectory of an initialized directory (root is $LOBSTER_PWD_ROOT)."
  if ! lobster_confirm "Are you sure?"; then
    lobster_failed
  fi
fi

# Make sure the config directory, if specified exists.
[ -d $(dirname "$lobster_app_config") ] || mkdir -p $(dirname "$lobster_app_config")
test -e "$LOBSTER_APP_ROOT/install/.config" && cp "$LOBSTER_APP_ROOT/install/.config" "$lobster_app_config" && lobster_success "Your app has been initialized."

lobster_include "post_init"
