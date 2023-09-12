#!/usr/bin/env bash

# @file
#
# Wrapper for shell hooks.
#
# This allows $HOOK files to NOT be executable.
# This allows us to provide API functions in wp_functions.sh.
#
# Returns the exit status of $HOOK (path to hook file).

source "$WEB_PACKAGE_ROOT/includes/wp_functions.sh"
cd "$ROOT" && source "$HOOK" "${@:1}"
