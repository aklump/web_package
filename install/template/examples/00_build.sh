#!/bin/bash
# 
# @file
# Processes build scripts without the use of web_package.
# 
# This is a file that can be moved to the root of a package and used to
# process the build scripts rather than having to install web_package.
# 
# It has a limitation in that the prev and the current version will always
# be the same since it has no context to know the difference.
# 

#
#
# Here is the configuration
#
php=$(which php)
bash=$(which bash)
build_scripts_dir=".web_package/hooks/build"
wp_info_file="web_package.info"
#
#
# End config
#

##
 # Return the a string from the info file
 # 
 # @param string the info key
 #
 # @return NULL
 #   Sets the value of global $get_info_string_return
 #
get_info_string_return='';
function get_info_string() {
  get_info_string_return=$(grep "$1" $wp_info_file | cut -f2 -d "=" | sed -e 's/^ *//g' -e 's/ *$//g');
  get_info_string_return=$(echo $get_info_string_return | sed -e 's/^[" ]*//g' -e 's/[" ]*$//g');
}

#
#
# Begin controller
#
if [[ -d "$build_scripts_dir" ]]; then

  project_root=$PWD

  # Loads in variables from our info file
  get_info_string 'version'
  version=$get_info_string_return
  prev=$version

  get_info_string 'name'
  get_name_return=$get_info_string_return
  
  get_info_string 'description'
  description=$get_info_string_return
  
  get_info_string 'homepage'
  homepage=$get_info_string_return
  
  get_info_string 'author'
  author=$get_info_string_return
  
  date=$(date)  

  for file in $(find "$build_scripts_dir" -type f); do
    cmd=''
    case ${file##*.} in
      'php' )
        cmd=$php
        ;;
      'sh' )
        cmd=$bash
        ;;
    esac

    # We will have a cmd if the file is recognized above
    if [[ "$cmd" ]]; then
      output=$($cmd $file "$prev" "$version" "$get_name_return" "$description" "$homepage" "$author" "$project_root" "$date" "$project_root/$wp_info_file")
      echo "`tput setaf 2`Calling $file...`tput op`"
      echo "`tput setaf 3`$output`tput op`"
    fi
  done
fi  
