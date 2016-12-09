#!/usr/bin/env bash

# By default try to use the action.
action=$(type $wp_hooklib_action >/dev/null 2>&1 && which $wp_hooklib_action)
if [ "$action" ]; then
    $action $wp_hooklib

# Otherwise just echo the path.
else
    lobster_success "The hook library can be found here:"
    lobster_success $wp_hooklib
fi
