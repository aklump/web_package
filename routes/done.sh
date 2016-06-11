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

# Get the develop to merge back into
if [ "$develop" ] && [ "$develop" != "$master" ]; then
  lobster_success "Merging into $develop (develop)..."
  $wp_git checkout "$develop"
  $wp_git merge --no-ff $get_branch_return -m "Merge branch '$get_branch_return'"

  # Try to merge into master
  echo
  read -n1 -p "Continue to $master (master)? (y/n) " a;
  echo
  if [ "$a" != 'y' ]
  then
    lobster_success 'CANCELLED!'
    $wp_git checkout $get_branch_return
    exit
  fi
fi

# Merge into master
if [ "$master" ]; then
  $wp_git checkout "$master"
  $wp_git merge --no-ff $get_branch_return -m "Merge branch '$get_branch_return'"

  # Delete the temp branch
  echo
  read -n1 -p "Delete $get_branch_return? (y/n) " a;
  echo
  if [ "$a" == 'y' ]; then
    # Delete the hotfix or release branch
    $wp_git br -d $get_branch_return
  fi
fi

# Tag the new release if we are supposed to for this severity
storage severity
do_tag=false;
if [ $wp_create_tags == 'patch' ]; then
  do_tag=true
fi
if [ $wp_create_tags == 'major' ] && [ $storage_return == 'major' ]; then
  do_tag=true
fi
if [ $wp_create_tags == 'minor' ]; then
  if [ $storage_return == 'major' ] || [ $storage_return == 'minor' ]; then
    do_tag=true
  fi
fi

if [ "$do_tag" == true ]; then
  get_version_with_prefix
  tagname=$get_version_with_prefix_return
  $wp_git tag $tagname
  lobster_success "Git tag created: $tagname"

  # Ask to push the tag to origin?
  a=''
  if [ "$wp_push_tags" == 'ask' ]; then
    echo
    read -n1 -p "git push $wp_remote $tagname? (y/n) " a;
    echo
  fi
  # Push the tag if auto or prompt was yes
  if [ "$a" == 'y' ] || [ "$wp_push_tags" == 'auto' ]; then
    if [ "$wp_push_tags" == 'auto' ]; then
      lobster_notice "AUTO: git push $wp_remote $tagname"
    fi
    $wp_git push $wp_remote $tagname
  fi
fi

# Ask to push the develop branch to origin?
a=''
if [ "$develop" ] && [ "$wp_push_develop" == 'ask' ]; then
  echo
  read -n1 -p "git push $wp_remote $develop? (y/n) " a;
  echo
fi
# Push the tag if auto or prompt was yes
if [ "$a" == 'y' ] || [ "$wp_push_develop" == 'auto' ]; then
  if [ "$wp_push_develop" == 'auto' ]; then
    lobster_notice "AUTO: git push $wp_remote $develop"
  fi
  $wp_git push $wp_remote $develop
fi

# Ask to push the master branch to origin?
a=''
if [ "$master" ] && [ "$wp_push_master" == 'ask' ]; then
  echo
  read -n1 -p "git push $wp_remote $master? (y/n) " a;
  echo
fi

# Push the tag if auto or prompt was yes
if [ "$a" == 'y' ] || [ "$wp_push_master" == 'auto' ]; then
  if [ "$wp_push_master" == 'auto' ]; then
    lobster_notice "AUTO: git push $wp_remote $master"
  fi
  $wp_git push $wp_remote $master
fi

# return to the original branch if not yet there
get_branch
if [ "$get_branch_return" != "$original_branch" ]; then
  $wp_git checkout $original_branch
fi

lobster_success "Welcome back to $original_branch!"
