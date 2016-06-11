#!/usr/bin/env bash
lobster_success "Current configuration:"
lobster_echo
lobster_echo "git = $wp_git"
lobster_echo "php = $wp_php"
lobster_echo "bash = $wp_bash"
lobster_echo "git_root = $wp_git_root"
lobster_echo "master = $wp_master"
lobster_echo "develop = $wp_develop"
lobster_echo "remote = $wp_remote"
if [[ "$wp_pause" ]]; then
  lobster_echo "pause = $wp_pause"
fi
lobster_echo "create_tags = $wp_create_tags"
lobster_echo "push_tags = $wp_push_tags"
lobster_echo "push_develop = $wp_push_develop"
lobster_echo "push_master = $wp_push_master"
lobster_echo "info_file = $wp_info_file"
lobster_echo "patch_prefix = $wp_patch_prefix"
lobster_echo "build = $wp_build"
lobster_echo "unbuild = $wp_unbuild"
lobster_echo "dev = $wp_dev"
#lobster_echo "php = $wp_php"
#lobster_echo "bash = $wp_patch_bash"
lobster_echo "major_step = $wp_major_step"
lobster_echo "minor_step = $wp_minor_step"
lobster_echo "patch_step = $wp_patch_step"
lobster_echo "author = $wp_author"
lobster_echo
