#!/usr/bin/env bash
lobster_access is_not_initialized

template=${lobster_args[1]};

# Copy the template folder as .web_package in target
rsync -a "$LOBSTER_APP_ROOT/install/template/" $LOBSTER_PWD/.web_package/ --exclude=tests
lobster_echo "For build script examples, take a look at .web_package/examples."

# Make the .gitignore file active by adding '.'
mv $LOBSTER_PWD/.web_package/gitignore $LOBSTER_PWD/.web_package/.gitignore

# If we have a template then load it
if [ "$template" ]; then
  load_config $template
  strstr $load_config_return $template
  if [ "$strstr_return" == true ]; then
    lobster_success "Template '$template' used."
  else
    lobster_failed "Template '$template' not found."
  fi
fi

conf=$LOBSTER_PWD/.web_package/config
if [ $template ]; then
  lobster_echo "template = $template" >> $conf
fi
lobster_echo "master = \"$wp_master\"" >> $conf
lobster_echo "develop = \"$wp_develop\"" >> $conf
lobster_echo "remote = $wp_remote" >> $conf
lobster_echo "create_tags = $wp_create_tags" >> $conf
lobster_echo "push_tags = $wp_push_tags" >> $conf
lobster_echo "push_master = $wp_push_master" >> $conf
lobster_echo "push_develop = $wp_push_develop" >> $conf
lobster_echo "patch_prefix = $wp_patch_prefix" >> $conf
lobster_echo "git_root = $PWD" >> $conf

# Restore the defaults
load_config

if lobster_has_param 'file'; then
  wp_info_file=$(lobster_get_param 'file')
else
  # Tweak the filetype based on certain flags
  declare -a array=('ini' 'json' 'yaml' 'yml');
  if lobster_has_params ${array[@]}; then
    wp_info_file="$base.$lobster_has_params_return";
  fi

  if lobster_has_param 'composer'; then
    wp_info_file="composer.json"
  fi
fi

lobster_echo "info_file = $wp_info_file" >> $conf

# Create the info file
if [ ! -s "$wp_info_file" ]; then
  read -e -p "Enter package name: " name
  put_info_string 'name' "$name"
  read -e -p "Enter package description: " description
  put_info_string 'description' "$description"
  read -e -p "Enter package homepage: " url
  if [ "$url" ]; then
    put_info_string 'homepage' "$url"
  fi
  put_info_string 'version' "$wp_init_version"

  # It may be that users don't want the author tag at all, so unless they
  # provide we will not write it to the .info file
  if [ "$wp_author" ]; then
    put_info_string 'author' $wp_author
  fi
fi

get_name
get_version
lobster_success "A new web_package \"$get_name_return\" (version: $get_version_return) has been created in the current directory."
