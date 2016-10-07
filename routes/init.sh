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
  echo "template = $template" >> $conf
fi
echo "master = \"$wp_master\"" >> $conf
echo "develop = \"$wp_develop\"" >> $conf
echo "remote = $wp_remote" >> $conf
echo "create_tags = $wp_create_tags" >> $conf
echo "push_tags = $wp_push_tags" >> $conf
echo "push_master = $wp_push_master" >> $conf
echo "push_develop = $wp_push_develop" >> $conf
echo "patch_prefix = $wp_patch_prefix" >> $conf
echo "git_root = $PWD" >> $conf

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

echo "info_file = $wp_info_file" >> $conf

# Create the info file
get_info_string 'name'
[[ "$get_info_string_return" ]] && lobster_input_return="$get_info_string_return" || lobster_input_return="tbd"
! lobster_has_param 'noinput' && lobster_input "Package name" "$get_info_string_return"
put_info_string 'name' "$lobster_input_return"

get_info_string 'description'
[[ "$get_info_string_return" ]] && lobster_input_return="$get_info_string_return" || lobster_input_return="tbd"
! lobster_has_param 'noinput' && lobster_input "Description" "$get_info_string_return"
put_info_string 'description' "$lobster_input_return"

get_info_string 'homepage'
[[ "$get_info_string_return" ]] && lobster_input_return="$get_info_string_return" || lobster_input_return="tbd"
! lobster_has_param 'noinput' && lobster_input "Homepage URL" "$get_info_string_return"
if [ "$input" ]; then
  put_info_string 'homepage' "$lobster_input_return"
fi

get_info_string 'version'
if [ ! "$get_info_string_return" ]; then
  get_info_string_return=$wp_init_version
fi
[[ "$get_info_string_return" ]] && lobster_input_return="$get_info_string_return" || lobster_input_return="tbd"
! lobster_has_param 'noinput' && lobster_input "Version" "$get_info_string_return"
put_info_string 'version' "$lobster_input_return"

get_info_string 'author'
if [ ! "$get_info_string_return" ]; then
  get_info_string_return="$wp_author"
fi
[[ "$get_info_string_return" ]] && lobster_input_return="$get_info_string_return" || lobster_input_return="tbd"
! lobster_has_param 'noinput' && lobster_input "Author" "$get_info_string_return"
# It may be that users don't want the author tag at all, so unless they
# provide we will not write it to the .info file
if [ "$wp_author" ]; then
  put_info_string 'author' "$wp_author"
fi



get_name
get_version
lobster_success "A new web_package \"$get_name_return\" (version: $get_version_return) has been created in the current directory."
