#!/usr/bin/env bash
lobster_access is_initialized

get_version
# $2 would be a target script
do_scripts "$wp_deploy" "$get_version_return" "$get_version_return" "$2" && lobster_success "Deployment scripts have been run." && exit 0
lobster_error "Deployment failed." && exit 1
