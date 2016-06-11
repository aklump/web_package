#!/usr/bin/env bash
lobster_access is_initialized

if lobster_confirm "Do you really want to DESTROY your web_package configuration and all hooks?"; then
  rm -r .web_package
  file=$(basename $wp_info_file)
  if test -e $wp_info_file && lobster_confirm "Destroy also $file?"; then
    rm $wp_info_file
  fi
fi

if ! test -e $wp_info_file && ! test -e .web_package; then
  lobster_success "Web package configuration completely destroyed."
else
  lobster_warning "Some configuration remains."
fi
