#!/usr/bin/env bash
lobster_access is_initialized

get_version
# $2 would be a target script
do_scripts "$wp_dev" "$get_version_return" "$get_version_return" "$2"
lobster_success "Dev mode has been enabled."
