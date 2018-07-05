#!/usr/bin/env bash
lobster_access is_initialized

wp_root="$project_root/.web_package"
tmp="$project_root/.web_package/tmp"


# Check for the tmp folder which indicates older file structure.
if [[ ! -d $tmp ]]; then
  lobster_verbose "Creating tmp and moving storage files."
  mkdir $tmp

  # Move persistent storage into tmp folder
  for i in $(find "$wp_root" -name '*.txt' -type f); do
    mv "$i" "$tmp/"
  done

  # Strip .txt extensions
  find "$tmp" -name '*.txt' -type f | while read NAME ; do mv "${NAME}" "${NAME%.txt}"; done
fi

if [[ ! -f $project_root/.web_package/.gitignore ]]; then
  lobster_echo "Creating .gitignore."
  lobster_echo 'tmp' > $project_root/.web_package/.gitignore
fi

if [ ! -d "$wp_root/hooks" ]; then
  rsync -a "$LOBSTER_APP_ROOT/install/template/hooks/" "$wp_root/hooks/"
  if [ -d "$wp_root/build" ]; then
    rm -r "$wp_root/hooks/build" && mv "$wp_root/build" "$wp_root/hooks/build"
  fi
fi

lobster_success "Update complete"
