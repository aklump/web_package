#!/usr/bin/env bash
lobster_access is_initialized
lobster_access bump_access

# All release_types except release should exclude hooks by default.
if ! lobster_has_param 'hooks' && ! lobster_has_param 'no-hooks' && [[ "$release_type" != 'release' ]]; then
  lobster_params=("${lobster_params[@]}" "no-hooks")
fi

get_version
version=$get_version_return
previous=$version

# Increment the version based on $severity level
increment_version $version $severity
version=$increment_version_return

if [ $previous == $version ]; then
  lobster_warning "Version unchanged: $previous ➡ $version"
else
  lobster_success "Version bumped: $previous ➡ $version"

  # Update the file with the new version string
  put_info_string 'version' "$version"

  # Look for build scripts and call
  lobster_has_param 'no-hooks' && lobster_notice "Skipping all hooks due to --no-hooks"
  if ! lobster_has_param 'no-hooks'; then

    # Fail if a build script script fails.
    if ! do_scripts "$wp_build" "$previous" "$version"; then
      put_info_string 'version' "$previous"
      lobster_success "Version rolled back to ➡ $previous"
      echo_build_failure && exit 1
    fi

    # Pause to allow for processing
    if [[ "$wp_pause" -lt 0 ]]; then
      read -n1 -p "$(tput setaf 3)Press any key to proceed...$(tput op)"
      echo
    elif [[ "$wp_pause" -gt 0 ]]; then
      lobster_echo "$(tput setaf 2)Waiting for $wp_pause seconds...$(tput op)"
      sleep $wp_pause
    fi
  fi
fi

if [ "$release_type" == 'hotfix' ] || [ "$release_type" == 'release' ]; then
  if [ ! "$wp_git_root" ] || ! test -e "$wp_git_root/.git"; then
    lobster_warning "Git support is disabled; no valid git root found."

  #
  #
  # Git Integration for hotfixes and release branches.
  #
  else
    starting_dir="$PWD"
    cd "$wp_git_root"

    # Store this branch so we can return to it when done
    get_branch
    storage return $get_branch_return
    storage severity $severity

    # Make note of the correct master/develop branches of this context
    is_master_branch
    is_develop_branch
    if [ "$is_master_branch_return" == true ]; then
      branches=($wp_master)
    elif [ "$is_develop_branch_return" == true ]; then
      branches=($wp_develop)
    fi

    # Locate this index and then match
    i=0
    for branch in "${branches[@]}"; do
      if [ "$branch" == "$get_branch_return" ]; then
        master_branches=($wp_master)
        develop_branches=($wp_develop)
        storage master ${master_branches[$i]}
        storage develop ${develop_branches[$i]}
      fi
      ((i++))
    done

    # Create the new release, add the release data, and commit
    get_version_with_prefix
    $wp_git checkout -b "$release_type-$get_version_with_prefix_return"
    $wp_git add -u

    # Calculate a commit message based on configuration.
    message=''
    if [[ "$release_type" == 'hotfix' ]] && [[ "$wp_hotfix_commit_message" ]]; then
      message="$wp_hotfix_commit_message"
      message="${message//PREVIOUS/$previous}"
      message="${message//VERSION/$version}"
      $wp_git add -u
      $wp_git commit -m "$message"
    elif [[ "$wp_do_version_commit" == true ]]; then
      $wp_git add -u
      $wp_git commit -m "Version bumped from $previous to $version"
    fi

    storage release_type "$release_type"
    storage previous "$previous"

    cd "$starting_dir"
  fi
fi
