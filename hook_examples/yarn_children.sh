#!/usr/bin/env bash

#
# Recurse in components and when you find yarn.lock, run yarn
#

set -e

# The directory containing the components.
# We assume the script is called from the theme root or that components is relative to it.
# Given the path of the script, we might need to be careful about the working directory.
# Script path: themes/custom/se_theme/.web_package/hooks/dev/00_yarn.sh
# Theme root: themes/custom/se_theme/

# Find all yarn.lock files in the components directory and run yarn in those directories.
# We use -maxdepth for components logic if needed, but the prompt says "recurse in components".

# Get the theme root directory relative to this script
THEME_ROOT="$PWD"

if [ ! -d "$THEME_ROOT/components" ]; then
  echo "Components directory not found at $THEME_ROOT/components"
  exit 1
fi

find "$THEME_ROOT/components" -name "yarn.lock" | while read -r lockfile; do
  component_dir=$(dirname "$lockfile")
  echo "Found yarn.lock in $component_dir. Running yarn..."
  (cd "$component_dir" && yarn install)
done
