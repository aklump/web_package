#!/bin/bash
#
# @file
# Provides common functions to .web_package scripts

# Use to indicate hook failed, but not build.
#
# $1 - string The message to echo.
#
# Exits 1
function hook_exception() {
  echo "$1"
  exit 1
}

# Use to indicate hook and build failed.
#
# $1 - string The message to echo.
#
# Exits 2
function build_fail_exception() {
  echo "$1"
  exit 2
}

#
# Duplicates (overwrites) folders or files from one point to another.
#
# @param string $from A path or filename as the source.
# @param string $to A path or filename as the destination.
#
function wp_duplicate() {
  local from="$1"
  local to="$2"
  local basename_from="$(basename "$from")"
  local basename_to="$basename_from"
  if [[ ! -d "$to" ]]; then
    local basename_to="$(basename "$to")"
  fi

  if [[ ! -e "$from" ]]; then
    echo "$(tty -s && tput setaf 1)$from does not exist.$(tty -s && tput op)"
    return 1
  fi

  if [[ -f "$from" ]]; then
    if ([[ -d "$(dirname "$to")" ]] || mkdir -p "$(dirname "$to")") && cp "$from" "$to"; then
      echo "$(tty -s && tput setaf 2)$basename_from duplicated as $basename_to.$(tty -s && tput op)"
      return 0
    fi
  elif [[ -d "$from" ]]; then
    if ([[ -d "$to" ]] || mkdir -p "$to") && rsync -a "$from/" "$to/"; then
      echo "$(tty -s && tput setaf 2)$basename_from duplicated as $basename_to.$(tty -s && tput op)"
      return 0
    fi
  fi

  echo "$(tty -s && tput setaf 1)Failed duplicating $basename_from.$(tty -s && tput op)"
  return 1
}

#
# Same as wp_duplicate but only if the destination doesn't exist already.
#
# @param string $from A path or filename as the source.
# @param string $to A path or filename as the destination.
#
function wp_duplicate_if_not_exists() {
  local from=$1
  local to=$2
  local to_file=$(basename $to)

  if [ -e "$to" ]; then
    echo "$(tty -s && tput setaf 3)$to_file already exists.$(tty -s && tput op)"
    return 1
  fi

  if wp_duplicate "$from" "$to"; then
    return 0
  fi

  return 1
}

#
# Wait for a file or folder to exist
#
# @param string Full path to a file or folder
#
function wp_wait_for_exists() {
  breakout=0
  while [[ ! -e "$1" ]] && [[ $breakout -lt 10 ]]; do
    ((breakout++))
    sleep 1
    echo waiting...$breakout
  done
  if [[ ! -e "$1" ]]; then
    echo
    echo "$1 does not exist.  Timed out."
    return 1
  fi
}

# Remove a file, or folder and it's contents.
#
# Use this instead of rm as it includes extra features pertinent to hooks.
#
# $1 - string Path to the file or folder.
#
# Returns nothing.
function wp_rm() {
  local path="$1"

  echo "Deleting: $path"
  if [[ -d "$path" ]]; then
    rm -r "$path" || build_fail_exception
  elif [[ -f "$path" ]]; then
    rm "$path" || build_fail_exception
  fi
}
