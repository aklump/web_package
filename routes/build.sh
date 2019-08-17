#!/usr/bin/env bash
lobster_access is_initialized

get_version
# $2 would be a target script
lobster_has_param 'no-hooks' && lobster_notice "Skipping all hooks due to --no-hooks"
! lobster_has_param 'no-hooks' && do_scripts "$wp_build" "$get_version_return" "$get_version_return" "$2"

if [[ $? -eq 0 ]]; then
  lobster_success "Build complete."
else
  lobster_error "BUILD HAS FAILED!" && exit 1
fi
