#!/usr/bin/env bash
lobster_access is_initialized
lobster_access done_access

# Do not remove
get_branch

storage return
original_branch=$storage_return
storage develop
develop=$storage_return
storage master
master=$storage_return
storage release_type
release_type=$storage_return
storage previous
previous=$storage_return
get_version
version=$get_version_return

message=''
if [[ "$release_type" == 'release' ]] && [[ "$wp_release_commit_message" ]]; then
  echo "committing..."
  message="$wp_release_commit_message"
  message="${message//PREVIOUS/$previous}"
  message="${message//VERSION/$version}"
  $wp_git add -u
  $wp_git commit -m "$message"
fi

# Get the develop to merge back into
if [[ "$develop" ]] && [[ "$develop" != "$master" ]]; then
  lobster_success "Merging into $develop (develop)..."
  $wp_git checkout "$develop"
  $wp_git merge --no-ff $get_branch_return -m "Merge branch '$get_branch_return'"

  # Try to merge into master
  if ! lobster_has_flag "y" && ! lobster_confirm "Continue to $master (master)"; then
    $wp_git checkout $get_branch_return
    lobster_failed
  fi
fi

# Merge into master
if [[ "$master" ]]; then
  lobster_success "Merging into $master (master)..."
  $wp_git checkout "$master"
  $wp_git merge --no-ff $get_branch_return -m "Merge branch '$get_branch_return'"

  # Delete the temp branch
  if lobster_has_flag "y" || lobster_confirm "Delete $get_branch_return"; then
    # Delete the hotfix or release branch
    if ! $wp_git branch -d $get_branch_return; then
      echo
      lobster_error "Process aborted before it was complete."
      echo
      exit 1
    fi
  fi
fi

# Tag the new release if we are supposed to for this severity
storage severity
do_tag=false;
if [ "$wp_create_tags" == 'patch' ]; then
  do_tag=true
fi
if [ "$wp_create_tags" == 'major' ] && [ "$storage_return" == 'major' ]; then
  do_tag=true
fi
if [ "$wp_create_tags" == 'minor' ]; then
  if [ "$storage_return" == 'major' ] || [ "$storage_return" == 'minor' ]; then
    do_tag=true
  fi
fi

if [ "$do_tag" == true ]; then
  get_version_with_prefix
  tagname=$get_version_with_prefix_return

  # Check if the tag already exists, which happens if the process had to abort
  # and was restarted.
  if $wp_git rev-parse "$tagname" >/dev/null 2>&1; then
    if ! lobster_confirm "$tagname already exists; overwrite it"; then
      echo
      lobster_error "Process aborted before it was complete."
      echo
      exit 1
    fi
    $wp_git tag -d "$tagname"
  fi

  if ! $wp_git tag "$tagname"; then
    echo
    lobster_error "Tag was not created; you will need to manually clean this up."
    echo
    exit 1
  fi
  lobster_success "Git tag created: $tagname"
  # Ask to push the tag to origin?
  if [ "$wp_push_tags" != 'no' ] && ([ "$wp_push_tags" == 'auto' ] || lobster_has_flag "y" || lobster_confirm "Push tag ($tagname) to $wp_remote"); then
    if [ "$wp_push_tags" == 'auto' ]; then
      lobster_notice "AUTO: git push $wp_remote $tagname"
    fi
    if ! $wp_git push $wp_remote $tagname; then
        lobster_error "Define a remote and try again, or set wp_push_tags = no"
    fi
  fi
fi

# Ask to push the develop branch to origin?
if [[ "$develop" ]]; then
  if [ "$wp_push_develop" != 'no' ] && ([ "$wp_push_develop" == 'auto' ] || lobster_has_flag "y" || lobster_confirm "Push develop branch ($develop) to $wp_remote"); then
    if [[ "$wp_push_develop" == 'ask' ]]; then
      lobster_notice "To suppress this question use the -y flag or set the configuration push_develop to auto."
    fi
    if [ "$wp_push_develop" == 'auto' ]; then
      lobster_notice "AUTO: git push $wp_remote $develop"
    fi
    if ! $wp_git push $wp_remote $develop; then
        lobster_error "Define a remote and try again, or set wp_push_develop = no"
    fi
  fi
fi

# Ask to push the master branch to origin?
if [ "$wp_push_master" != 'no' ] && ([ "$wp_push_master" == 'auto' ] || lobster_has_flag "y" || lobster_confirm "Push master branch ($master) to $wp_remote"); then
  if [[ "$wp_push_master" == 'ask' ]]; then
      lobster_notice "To suppress this question use the -y flag or set the configuration push_master to auto."
    fi
  if [ "$wp_push_master" == 'auto' ]; then
    lobster_notice "AUTO: git push $wp_remote $master"
  fi
  if ! $wp_git push $wp_remote $master; then
      lobster_error "Define a remote and try again, or set wp_push_master = no"
  fi
fi

if [[ "$wp_push_tags" == 'ask' ]] || [[ "$wp_push_master" == 'ask' ]] || [[ "$wp_push_develop" == 'ask' ]]; then
  lobster_notice "(To avoid push confirmations, use the -y flag or set the values of push_* to auto or no in your configuration.)"
fi

if lobster_has_param 'no-hooks'; then
    lobster_notice "Skipping deploy hooks due to --no-hooks"
else
    get_version
    do_scripts "$wp_deploy" "$get_version_return" "$get_version_return"
fi

# Return to the original branch if not yet there
get_branch
if [ "$get_branch_return" != "$original_branch" ]; then
  $wp_git checkout $original_branch
fi

if [ "$original_branch" ]; then
    lobster_success "Welcome back to $original_branch!"
fi
