#!/bin/bash

#
# @file
# Increments filename version and creates new branch
#
# USAGE:
#   See README.md
#
# CREDITS:
# In the Loft Studios
# Aaron Klump - Web Developer
# PO Box 29294 Bellingham, WA 98228-1294
# aim: theloft101
# skype: intheloftstudios
#
#
# LICENSE:
# Copyright (c) 2012, In the Loft Studios, LLC. All rights reserved.
#
# Redistribution and use in source and binary forms, with or without
# modification, are permitted provided that the following conditions are met:
#
#   1. Redistributions of source code must retain the above copyright notice,
#   this list of conditions and the following disclaimer.
#
#   2. Redistributions in binary form must reproduce the above copyright notice,
#   this list of conditions and the following disclaimer in the documentation
#   and/or other materials provided with the distribution.
#
#   3. Neither the name of In the Loft Studios, LLC, nor the names of its
#   contributors may be used to endorse or promote products derived from this
#   software without specific prior written permission.
#
# THIS SOFTWARE IS PROVIDED BY IN THE LOFT STUDIOS, LLC "AS IS" AND ANY EXPRESS
# OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES
# OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO
# EVENT SHALL IN THE LOFT STUDIOS, LLC OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
# INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
# BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
# DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY
# OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
# NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE,
# EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
#
# The views and conclusions contained in the software and documentation are
# those of the authors and should not be interpreted as representing official
# policies, either expressed or implied, of In the Loft Studios, LLC.
#
#
# @ingroup loft_git Loft Git
# @{
#
source="${BASH_SOURCE[0]}"
while [ -h "$source" ]; do # resolve $source until the file is no longer a symlink
  dir="$( cd -P "$( dirname "$source" )" && pwd )"
  source="$(readlink "$source")"
  [[ $source != /* ]] && source="$dir/$source" # if $source was a relative symlink, we need to resolve it relative to the path where the symlink file was located
done
root="$( cd -P "$( dirname "$source" )" && pwd )"

##
 # BEGIN CONFIGURATION
 #

#
# Determine the root folder of the project; where .web_package lives
#
project_root=${PWD}

##
 # The name of the branch servering "master" role
 #
wp_master="master"

##
 # The name of the branch servering "develop" role
 #
wp_develop="develop"

# For use with Drupal do this...
#wp_master="8.x-1.x 7.x-1.x 6.x-1.x 5.x-1.x"
#wp_develop="8.x-1.x 7.x-1.x 6.x-1.x 5.x-1.x"

##
 # The name of the git remote
 #
wp_remote=origin

##
 # Whether to create tags or not
 # Values: major, minor, patch or no
 #
wp_create_tags=minor

##
 # Should tags be pushed to remote?
 # Allowed values: ask, auto, no
 #
wp_push_tags=ask

##
 # Should the develop branch be pushed to remote on bump done?
 # Allowed values: ask, auto, no
 #
wp_push_develop=ask

##
 # Should the master branch be pushed to remote on bump done?
 # Allowed values: ask, auto, no
 #
wp_push_master=ask

##
 # The name of the file that holds your version string
 #
wp_info_file='web_package.info';
if [ ! -f "$wp_info_file" ]; then
  for file in $(ls *.info 2>/dev/null)
  do
    wp_info_file=$file
    break;
  done
fi

##
 # The name of the project author
 #
wp_author=''

##
 # The initial version for new projects
 #
wp_init_version="7.x-1.0-alpha1"

##
 # The default patch prefix when bumping patch from a version that doesn't
 # contain patch data, e.g. 1.0 ---> 1.0${wp_patch_prefix}1
 #
wp_patch_prefix='.'

##
 # The default value for version stepping.
 # Can also be "odd" or "even".
 #
wp_major_step=1
wp_minor_step=1
wp_patch_step=1

##
 # The default number of seconds to sleep
 # 
wp_pause=5

##
 # The directory to scan for build scripts
 # 
wp_build=./.web_package/hooks/build/

##
 # The directory to scan for build scripts
 # 
wp_unbuild=./.web_package/hooks/unbuild/

##
 # The directory to scan for build scripts
 # 
wp_dev=./.web_package/hooks/dev/

##
 # BEGIN FUNCTIONS
 #

##
 # End execution with a message
 #
 # @param string $1
 #   A message to display;
 #
function end() {
  echo
  echo $1
  echo
  exit;
}

##
 # Load the configuration file
 #
 # Lines that begin with [ or # will be ignored
 # Format: Name = "Value"
 # Value does not need wrapping quotes if no spaces
 #
 # @param string $1
 #  (Optional) Defaults to ''.  Config file suffix.
 #
 # @return NULL
 #   Sets value of load_config_return with the master config path
 #
load_config_return=''
function load_config() {
  load_config_return=''

  # FIRST CHOICE: Take the template name from argument 1
  if [ $# -eq 1 ]
  then
    wp_template=$1
  else
    # SECOND CHOICE: Take the template name from the project config
    parse_config .web_package/config
  fi

  # Decide if we have template
  # todo get the template defaults based on local config template
  load_config_return=$HOME/.web_package/config

  # If we have an master template file use it as source instead
  template_file=$HOME/.web_package/config_$wp_template
  if [ -f "$template_file" ]; then
    load_config_return=$template_file
  fi

  # Load user config
  parse_config $load_config_return

  # Load user local config
  config=$HOME/.web_package/local_config
  if [ -f "$config" ]; then
    parse_config $config
  fi

  # Load project config
  parse_config .web_package/config

  # Load project local config
  config=.web_package/local_config
  if [ -f "$config" ]; then
    parse_config $config
  fi


  if [[ ! "$wp_php" ]]; then
    wp_php=$(which php)
  fi

  if [[ ! "$wp_bash" ]]; then
    wp_bash=$(which bash)
  fi

  # Legacy support convert micro to patch and alert user
  if [[ "$wp_create_tags" == 'micro' ]]; then
    wp_create_tags='patch';
    echo "`tput setaf 3`Replace 'create_tags = micro' with 'create_tags = patch' in .web_package/config`tput op`"
  fi
}

##
 # Parse a config file
 #
 # @param string $1
 #   The filepath of the config file
 #
function parse_config() {
  if [ -f $1 ]
  then
    while read line; do
      if [[ "$line" =~ ^[^#[]+ ]]; then
        name=${line%% =*}
        value=${line##*= }
        if [ "$name" ]
        then
          eval wp_$name=$value
        fi
      fi
    done < $1
  fi
}

# If .web_package/config then we can override defaults, but optional.
load_config

##
 # Initialize a new web_package directory
 #
 # @param string $1
 #   (Optional) Defaults to ''.  Config file suffix.
 #
 # @return NULL
 #
function do_init() {
  if [ ! -d .web_package ]; then
    # Copy the template folder as .web_package in target
    rsync -a "$root/template/" ./.web_package/ --exclude=tests
    echo "`tput setaf 3`For build script examples, take a look at .web_package/examples.`tput op`"

    # Make the .gitignore file active by adding '.'
    mv ./.web_package/gitignore ./.web_package/.gitignore

    # If we have a template then load it
    template=false
    if [ "$1" ]
    then
      load_config $1
      strstr $load_config_return $1
      if [ "$strstr_return" == true ]
      then
        echo "Template `tput setaf 2`$1`tput op` used."
        template=$1
      else
        echo "Template `tput setaf 1`$1`tput op` not found."
      fi
    fi

    if [ $template ]
    then
      echo "template = $template" >> .web_package/config
    fi
    echo "master = \"$wp_master\"" >> .web_package/config
    echo "develop = \"$wp_develop\"" >> .web_package/config
    echo "remote = $wp_remote" >> .web_package/config
    echo "create_tags = $wp_create_tags" >> .web_package/config
    echo "push_tags = $wp_push_tags" >> .web_package/config
    echo "push_master = $wp_push_master" >> .web_package/config
    echo "push_develop = $wp_push_develop" >> .web_package/config
    echo "patch_prefix = $wp_patch_prefix" >> .web_package/config

    # Restore the defaults
    load_config
  fi

  # Create the info file
  if [ ! -s "$wp_info_file" ]
  then
    read -e -p "Enter package name: " name
    read -e -p "Enter package description: " description
    read -e -p "Enter package homepage: " url
    echo "name = \"$name\"" > $wp_info_file
    echo "description = \"$description\"" >> $wp_info_file
    if [ "$url" ]; then
      echo "homepage = $url" >> $wp_info_file
    fi
    echo "version = $wp_init_version" >> $wp_info_file

    # It may be that users don't want the author tag at all, so unless they
    # provide we will not write it to the .info file
    if [ "$wp_author" ]
    then
      echo "author = $wp_author" >> $wp_info_file
    fi

  fi

  get_name
  get_version
  end "A new web_package \"$get_name_return\" (version: $get_version_return) has been created."
}

#
# Look for and call build scripts
# 
# @param $previous string The previous version
# @param $version string The current version
# @param $target_script Optional. Specify a single target script in the $dir,
#   otherwise all scripts will be searched.
# 
# @return 0 if build occurred, 1 otherwise
# 
function do_scripts() {
  local dir=$1
  if [ "$dir" ] && [[ -d "$dir" ]]; then
    local prev=$2
    local version=$3
    get_name
    get_info_string 'description'
    local description=$get_info_string_return
    get_info_string 'homepage'
    local homepage=$get_info_string_return
    get_info_string 'author'
    local author=$get_info_string_return
    local date=$(date)

    local target_scripts="$(find "$dir")"
    # Check to see if a scriptname has been provided, instead.
    if [[ "$4" ]]; then
      echo "Looking for provided file: '$4'"
      target_scripts="$dir/$4"
    fi
    for file in ${target_scripts[@]}; do
      if ! test -e "$file"; then
        echo "`tty -s && tput setaf 1`wp error: $dir`tty -s && tput op`"
        echo "`tty -s && tput setaf 1`detected hook file: '$file' doesn't exist!`tty -s && tput op`"
      else
        local cmd=''
        if [[ ${file##*.} == 'php' ]]; then
          cmd=$wp_php
        elif [[ ${file##*.} == 'sh' ]]; then
          cmd=$wp_bash
        fi
        if [[ "$cmd" ]]; then
          output=$($cmd $file "$prev" "$version" "$get_name_return" "$description" "$homepage" "$author" "$project_root" "$date" "$project_root/$wp_info_file" "$dir" "$project_root/.web_package")
          echo "`tput setaf 2`Calling $file...`tput op`"
          echo "`tput setaf 3`$output`tput op`"
        fi
      fi
    done
    return 0
  fi  

  return 1
}

function do_check_update_needed() {
  local needed=0
  if [[ ! -d "$project_root/.web_package/tmp" ]]; then
    needed=1
  fi

  if [[ ! -d "$project_root/.web_package/hooks" ]]; then
    needed=1
  fi

  if [[ $needed -eq 1 ]]; then
    end "`tput setaf 3`Update required; call 'bump update'`tput op`"
  fi
}

#
# Automatic updates between versions
# 
# This script should never assume a version and she act accordingly
# 
function do_update() {
  local wp_root="$project_root/.web_package"
  local tmp="$project_root/.web_package/tmp"
  
  if [[ ! -d $tmp ]]; then
    echo "`tput setaf 2`Creating tmp and moving storage files.`tput op`"
    mkdir -v $tmp

    # Move persistent storage into tmp folder
    for i in $(find "$wp_root" -name '*.txt' -type f); do
      mv "$i" "$tmp/"
    done

    # Strip .txt extensions
    find "$tmp" -name '*.txt' -type f | while read NAME ; do mv "${NAME}" "${NAME%.txt}"; done
  fi

  if [[ ! -f $project_root/.web_package/.gitignore ]]; then
    echo "`tput setaf 2`Creating .gitignore.`tput op`"
    echo 'tmp' > $project_root/.web_package/.gitignore
  fi

  if [ ! -d "$wp_root/hooks" ]; then
    rsync -a "$root/template/hooks/" "$wp_root/hooks/"
    if [ -d "$wp_root/build" ]; then
      rm -r "$wp_root/hooks/build" && mv "$wp_root/build" "$wp_root/hooks/build"
    fi
  fi
}

##
 # Test all version bumping
 #
 # @return NULL
 #
function do_test() {
  if [ "$1" ]
  then
    test_version "$1" "$2" "$3" "$4" "$5" "$6" "$7"
    return
  fi

  staged=$wp_patch_prefix;
  as_test=''

  # standard versions
  echo 'MAJOR.MINOR.PATCH:'
  wp_patch_prefix='.'
  test_version 0.0.1 0.0.2 0.1 1.0 '0.1-alpha1' '0.1-beta1' '0.1-rc1'
  test_version "0.1" "0.1.1" "0.2" "1.0" "0.2-alpha1" "0.2-beta1" "0.2-rc1"
  test_version 1.0 1.0.1 1.1 2.0 '1.1-alpha1' '1.1-beta1' '1.1-rc1'
  test_version 1 1.0.1 1.1 2.0 '1.1-alpha1' '1.1-beta1' '1.1-rc1'
  test_version 9.9.9 9.9.10 9.10 10.0 "9.10-alpha9" "9.10-beta9" "9.10-rc9"
  test_version 99.99.99 99.99.100 99.100 100.0 "99.100-alpha99" "99.100-beta99" "99.100-rc99"

  echo 'MAJOR.MINOR(MINOR_PREFIX)PATCH:'
  wp_patch_prefix='-alpha'
  test_version "0.3${wp_patch_prefix}4" "0.3${wp_patch_prefix}5" "0.3" "1.0${wp_patch_prefix}1" "0.3${wp_patch_prefix}4" "0.3-beta1" "0.3-rc1"


  test_version 0.1 "0.2${wp_patch_prefix}1" "0.2" "1.0" "0.2-alpha1" "0.2-beta1" "0.2-rc1"
  test_version 1.0 "1.1${wp_patch_prefix}1" "1.1" "2.0" "1.1-alpha1" "1.1-beta1" "1.1-rc1"
  test_version 1 "1.1${wp_patch_prefix}1" "1.1" "2.0" "1.1-alpha1" "1.1-beta1" "1.1-rc1"
  #test_version 9.9.9 9.9.10 9.10 10.0
  #test_version 99.99.99 99.99.100 99.100 100.0

  # Drupal

  echo 'DRUPAL STYLE:'
  wp_patch_prefix='-alpha'
  test_version 7.x-1.0-alpha1 7.x-1.0-alpha2 7.x-1.0 7.x-2.0-alpha1 7.x-1.0-alpha1 7.x-1.0-beta1 7.x-1.0-rc1
  test_version 7.x-1.0-alpha217 7.x-1.0-alpha218 7.x-1.0 7.x-2.0-alpha1 7.x-1.0-alpha217 7.x-1.0-beta1 7.x-1.0-rc1
  test_version 7.x-1.0 7.x-1.1${wp_patch_prefix}1 7.x-1.1 7.x-2.0 7.x-1.1-alpha1 7.x-1.1-beta1 7.x-1.1-rc1

  echo 'DRUPAL STYLE:'
  wp_patch_prefix='-beta'
  test_version 7.x-1.0-alpha9 7.x-1.0-alpha10 7.x-1.0 7.x-2.0-alpha1 7.x-1.0-alpha9 7.x-1.0-beta1 7.x-1.0-rc1
  test_version 7.x-1.0 7.x-1.1${wp_patch_prefix}1 7.x-1.1 7.x-2.0 7.x-1.1-alpha1 7.x-1.1-beta1 7.x-1.1-rc1

  echo 'DRUPAL STYLE:'
  wp_patch_prefix='-rc'
  test_version 7.x-1.0-alpha1 7.x-1.0-alpha2 7.x-1.0 7.x-2.0-alpha1 7.x-1.0-alpha1 7.x-1.0-beta1 7.x-1.0-rc1
  test_version 7.x-1.0 7.x-1.1${wp_patch_prefix}1 7.x-1.1 7.x-2.0 7.x-1.1-alpha1 7.x-1.1-beta1 7.x-1.1-rc1

  # source: http://drupal.org/node/1015226
  test_version 7.0 7.1${wp_patch_prefix}1 7.1 8.0 7.1-alpha1 7.1-beta1 7.1-rc1
  test_version 8.0-beta1 8.0-beta2 8.0 9.0-beta1 8.0-beta1 8.0-beta1 8.0-rc1
  test_version 7.x-2.3 7.x-2.4${wp_patch_prefix}1 7.x-2.4 7.x-3.0 7.x-2.4-alpha1 7.x-2.4-beta1 7.x-2.4-rc1
  test_version 8.x-2.0-alpha6 8.x-2.0-alpha7 8.x-2.0 8.x-3.0-alpha1 8.x-2.0-alpha6 8.x-2.0-beta1 8.x-2.0-rc1

  test_version 2.3-rc5 2.3-rc6 2.3 3.0-rc1 2.3-rc5 2.3-rc5 2.3-rc5
  test_version 2.3-beta5 2.3-beta6 2.3 3.0-beta1 2.3-beta5 2.3-beta5 2.3-rc1

  #@todo These need work
  #_p='my.pre_fix1-'
  #_mp='mi.cro_prefix-'
  #
  #echo ODD PREFIX:
  #test_version ${_p}0.0.1 ${_p}0.0.2 ${_p}0.1 ${_p}1.0
  #test_version ${_p}0.1 ${_p}0.1.1 ${_p}0.2 ${_p}2.0
  #test_version ${_p}1 ${_p}1.0.1 ${_p}1.1 ${_p}2.0
  #
  #echo ODD PATCH_PREFIX:
  #test_version 1.0${_mp}1 1.0${_mp}2 1.0 2.0
  #
  #echo ODD PREFIX AND PATCH_PREFIX
  #test_version ${_p}0.0.1${_mp}
  #test_version ${_p}0.1${_mp}
  #test_version ${_p}1${_mp}

  wp_patch_prefix=$staged

}

##
 # A test for a single version number
 #
 # @param string $1
 #   The version to test
 # @param string $2
 #   The expected patch result
 # @param string $3
 #   The expected minor result
 # @param string $4
 #   The expected major result
 # @param string $5
 #   The expected alpha result
 # @param string $6
 #   The expected beta result
 # @param string $7
 #   The expected rc result
 #
 # @return NULL
 #
function test_version() {
  as_test="bump test $1"
  test_version_severity $1 patch $2
  as_test="$as_test $increment_version_return"

  test_version_severity $1 minor $3
  as_test="$as_test $increment_version_return"

  test_version_severity $1 major $4
  as_test="$as_test $increment_version_return"

  test_version_severity $1 alpha $5
  as_test="$as_test $increment_version_return"

  test_version_severity $1 beta $6
  as_test="$as_test $increment_version_return"

  test_version_severity $1 rc $7
  as_test="$as_test $increment_version_return"

  echo
  echo "`tty -s && tput setaf 3`$as_test`tty -s && tput op`"
}

##
 # Test a single version by severity
 #
 # @param string $1
 #   The version string
 # @param string $2
 #   The severity level
 # @param string $3
 #   The control/expected value
 #
 # @return NULL
 #
function test_version_severity() {
  increment_version $1 $2
  result="`tput setaf 4`[No Tests]`tput op`"
  if [ "$3" ]
  then
    result="`tput setaf 2` [OK]`tput op`"
  fi
  if [ "$3" ] && [ "$increment_version_return" != "$3" ]
  then
    result="`tput setaf 1` != $3 [FAIL]`tput op`"
  fi
  printf "%-10s\n" "$2: $1 --> $increment_version_return $result"
}


#
# Checks if a version part is even.
#
function is_even () {
  num=$1
  return $((num%2))
}

###
 # Increment the version number
 #
 # This variable relies on a global: $version It should resemble this n.n.n, but
 # can also be n.n or just n, where n is any number
 #
 # @param string $1
 #   The version string to increment
 # @param string $2
 #   The severity of the increment: patch, minor, major, alpha, beta, rc
 #
 ##
increment_version_return=''
function increment_version () {
  increment_version_return='';

  # 7.x-1.0.1 || # 7.x-1.0-rc1
  regex1='([^-]+-)?([0-9]+)(.)([0-9]+)([^0-9]+)([0-9]+)'
  regex2='([^-]+-)?([0-9]+)(.)([0-9]+)'
  regex3='([^-]+-)?([0-9]+)'

  if [[ "$1" =~ $regex1 ]]
  then
    schema=1
    prefix="${BASH_REMATCH[1]}"
    major="${BASH_REMATCH[2]}"
    major_suffix="${BASH_REMATCH[3]}"
    minor="${BASH_REMATCH[4]}"
    patch_prefix="${BASH_REMATCH[5]}"
    patch="${BASH_REMATCH[6]}"

  # 7.x-1.0
  elif [[ "$1" =~ $regex2 ]]
  then
    schema=2
    prefix="${BASH_REMATCH[1]}"
    major="${BASH_REMATCH[2]}"
    major_suffix="${BASH_REMATCH[3]}"
    minor="${BASH_REMATCH[4]}"
    patch_prefix=''
    patch=0

  # 7.x-1
  elif [[ "$1" =~ $regex3 ]]
  then
    schema=3
    prefix="${BASH_REMATCH[1]}"
    major="${BASH_REMATCH[2]}"
    major_suffix='.'
    minor=0
    patch_prefix=''
    patch=0
  else
    end "'$1' uses an unknown version schema and cannot be incremented."
  fi

  
  major_step=$wp_major_step
  minor_step=$wp_minor_step
  patch_step=$wp_patch_step

  # Convert odds and evens to an int.
  odd_step=2;
  even_step=1;
  if is_even $patch; then
    odd_step=1;
    even_step=2;
  fi
    
  if [[ $major_step == 'odd' ]]; then
    major_step=$odd_step
  elif [[ $major_step == 'even' ]]; then
    major_step=$even_step
  fi

  if [[ $minor_step == 'odd' ]]; then
    minor_step=$odd_step
  elif [[ $minor_step == 'even' ]]; then
    minor_step=$even_step
  fi
  
  if [[ $patch_step == 'odd' ]]; then
    patch_step=$odd_step
  elif [[ $patch_step == 'even' ]]; then
    patch_step=$even_step
  fi
  # Done convert

  case "$2" in
    major)
      if [ "$patch_prefix" == '.' ] || [ "$patch_prefix" = '' ]
      then
        major=$(($major + $major_step))
        major_suffix='.'
        minor=0
        patch_prefix=''
        patch=''
      else
        major=$(($major + $major_step))
        major_suffix='.'
        minor=0
        patch=1
      fi

      ;;
    minor)
      # Only increment minor if the prefix is '.' or prefix is empty
      if [ "$patch_prefix" == '.' ] || [ ! "$patch_prefix" ]
      then
        minor=$(($minor + $minor_step))
      fi
      patch_prefix=''
      patch=''
      ;;
    patch)
      patch=$(($patch + $patch_step))
      if [ ! "$patch_prefix" ]
      then
        patch_prefix=$wp_patch_prefix
        #if you bump patch and theres no prefix and the default is not . then
        #you increment minor too
        if [ $wp_patch_prefix != '.' ]
        then
          minor=$(($minor + $minor_step))
        fi
      fi
      ;;
  esac

  if [ "$2" == 'alpha' ] || [ "$2" == 'beta' ] || [ "$2" == 'rc' ]
  then

    # Determine the current version state
    strstr $patch_prefix alpha
    is_alpha=$strstr_return
    strstr $patch_prefix beta
    is_beta=$strstr_return
    strstr $patch_prefix rc
    is_rc=$strstr_return

    if [[ "$patch_prefix" == '.' ]] || [ ! "$patch_prefix" ] || ([ $is_alpha == true ] && ([ $2 == 'beta' ] || [ $2 == 'rc' ])) || ([ $is_beta == true ] && [ $2 == 'rc' ])
    then
      if [ "$patch_prefix" != '.' ] || [ ! "$patch_prefix" ]
      then
        patch=1
      fi
      if [ "$patch_prefix" == '.' ] || [ ! "$patch_prefix" ] || [ "$2" == 'alpha' ]
      then
        minor=$(($minor + $minor_step))
      fi
      patch_prefix="-$2"
    fi
  fi

  increment_version_return="${prefix}${major}${major_suffix}${minor}${patch_prefix}${patch}"
  return;
}

##
 # Find first occurrence of a string
 #
 # @param string $1
 #   Haystack
 # @param string $2
 #   Needle
 #
 # @return NULL
 #   Sets the value of global $strstr_return
 #
 # @code
 #   strstr "$h" "$n"
 #   if [ $strstr_return == true ] ...
 # @endcode
 #
strstr_return=false;
function strstr() {
  strstr_return=false;
  if [ "$1" ] && `echo ${1} | grep "${2}" 1>/dev/null 2>&1`
  then
    strstr_return=true
  fi
}

##
 # Return the name of the current git branch
 #
 # @return NULL
 #   Sets the value of global $get_branch_return
 #
get_branch_return='';
function get_branch() {
  get_branch_return=''
  if in_git_repo; then
    get_branch_return=$(git branch | sed -n -e 's/^\* \(.*\)/\1/p')
  fi
}

#
# Test if we're in a git repo
#
# @code
#   if in_git_repo; then
# @endcode
#
function in_git_repo () {
  # Copyright (C) 2006,2007 Shawn O. Pearce <spearce@spearce.org>
  # Conceptually based on gitcompletion (http://gitweb.hawaga.org.uk/).
  # Distributed under the GNU General Public License, version 2.0.
  if [ -d .git ] || (git rev-parse --git-dir 2> /dev/null); then
    return 0;
  fi;

  return -1;
}

##
 # Check if we're on a master branch
 #
 # @return NULL
 #   Sets the value of global $is_master_branch_return to true or false
 #
is_master_branch_return=false;
function is_master_branch() {
  is_master_branch_return=false
  get_branch
  branches=($wp_master)
  for branch in "${branches[@]}"
  do
    if [ "$branch" == "$get_branch_return" ]
    then
      is_master_branch_return=true
      return
    fi
  done
}

##
 # Check if we're on a master branch
 #
 # @return NULL
 #   Sets the value of global $is_develop_branch_return to true or false
 #
is_develop_branch_return=false;
function is_develop_branch() {
  is_develop_branch_return=false
  get_branch
  branches=($wp_develop)
  for branch in "${branches[@]}"
  do
    if [ "$branch" == "$get_branch_return" ]
    then
      is_develop_branch_return=true
      return
    fi
  done
}

##
 # Return the current version
 #
 # @return NULL
 #   Sets the value of global $get_version_return
 #
get_version_return='';
function get_version() {
  get_version_with_prefix
  get_version_return=$get_version_with_prefix_return
}

##
 # Return the current version
 #
 # @return NULL
 #   Sets the value of global $get_version_with_prefix_return
 #
get_version_with_prefix_return='';
function get_version_with_prefix() {
  get_info_string 'version'
  get_version_with_prefix_return=$get_info_string_return
}

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
  get_info_string_return=$(grep -m 1 "$1" $wp_info_file | cut -f2 -d "=" | sed -e 's/^ *//g' -e 's/ *$//g');
  get_info_string_return=$(echo $get_info_string_return | sed -e 's/^[" ]*//g' -e 's/[" ]*$//g');
}

##
 # Return the name
 #
 # @return NULL
 #   Sets the value of global $get_name_return
 #
get_name_return='';
function get_name() {
  get_info_string 'name'
  get_name_return=$get_info_string_return
}

##
 # Getter/Setter for persistent stored vars
 #
 # The return branch is the branch from which the release type began, it is used
 # to return to after bump done as well as to know the dev/master pairing
 #
 # @param string $1
 #   the name of the storage variable
 # @param string $2
 #   the value to store
 #
 # @return NULL
 #   Sets the value of global $storage_return
 #
storage_return='';
function storage() {
  file=".web_package/tmp/$1"
  if [ "$2" ]
  then
    echo $2 > $file
  fi
  storage_return=`cat $file`
}

##
 # Do the done op
 #
 # @return NULL
 #
function do_done() {
  # Make sure we don't try to run 'bump done' on a master or develop branch
  is_master_branch
  if [ $is_master_branch_return == true ]
  then
    end 'You cannot finish a "master" branch. Aborted!'
  fi

  is_develop_branch
  if [ $is_develop_branch_return == true ]
  then
    end 'You cannot finish a "develop" branch. Aborted!'
  fi

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
    echo "Merging into $develop (develop)..."
    git co "$develop"
    git merge --no-ff $get_branch_return -m "Merge branch '$get_branch_return'"

    # Try to merge into master
    echo
    read -n1 -p "Continue to $master (master)? (y/n) " a;
    echo
    if [ "$a" != 'y' ]
    then
      echo 'CANCELLED!'
      git co $get_branch_return
      exit
    fi
  fi

  # Merge into master
  if [ "$master" ]; then
    git co "$master"
    git merge --no-ff $get_branch_return -m "Merge branch '$get_branch_return'"

    # Delete the temp branch
    echo
    read -n1 -p "Delete $get_branch_return? (y/n) " a;
    echo
    if [ "$a" == 'y' ]; then
      # Delete the hotfix or release branch
      git br -d $get_branch_return
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
  if [ $wp_create_tags == 'minor' ]
  then
    if [ $storage_return == 'major' ] || [ $storage_return == 'minor' ]; then
      do_tag=true
    fi
  fi

  if [ "$do_tag" == true ]; then
    get_version_with_prefix
    tagname=$get_version_with_prefix_return
    git tag $tagname
    echo
    echo "Git tag created: $tagname"

    # Ask to push the tag to origin?
    a=''
    if [ "$wp_push_tags" == 'ask' ]
    then
      echo
      read -n1 -p "git push $wp_remote $tagname? (y/n) " a;
      echo
    fi
    # Push the tag if auto or prompt was yes
    if [ "$a" == 'y' ] || [ "$wp_push_tags" == 'auto' ]
    then
      if [ "$wp_push_tags" == 'auto' ]
      then
        echo
        echo "AUTO: git push $wp_remote $tagname"
      fi
      git push $wp_remote $tagname
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
    if [ "$wp_push_develop" == 'auto' ]
    then
      echo
      echo "AUTO: git push $wp_remote $develop"
    fi
    git push $wp_remote $develop
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
    if [ "$wp_push_master" == 'auto' ]
    then
      echo
      echo "AUTO: git push $wp_remote $master"
    fi
    git push $wp_remote $master
  fi

  # return to the original branch if not yet there
  get_branch
  if [ "$get_branch_return" != "$original_branch" ]; then
    git co $original_branch
  fi
  end "Welcome back to $original_branch!"
}

##
 # Show Info if requested
 #
if [ "$1" == 'config' ]; then
  echo "master = $wp_master"
  echo "develop = $wp_develop"
  echo "remote = $wp_remote"
  if [[ "$wp_pause" ]]; then
    echo "pause = $wp_pause"
  fi
  echo "create_tags = $wp_create_tags"
  echo "push_tags = $wp_push_tags"
  echo "push_develop = $wp_push_develop"
  echo "push_master = $wp_push_master"
  echo "info_file = $wp_info_file"
  echo "patch_prefix = $wp_patch_prefix"
  echo "build = $wp_build"
  echo "unbuild = $wp_unbuild"
  echo "dev = $wp_dev"
  #echo "php = $wp_php"
  #echo "bash = $wp_patch_bash"
  echo "major_step = $wp_major_step"
  echo "minor_step = $wp_minor_step"
  echo "patch_step = $wp_patch_step"
  echo "author = $wp_author"
  exit
fi

##
 # Show Version if requested
 #
if [ "$1" == 'init' ]; then
  do_init $2
fi





##
 # Anything below here must have a .info file
 #

if [ ! -f "$wp_info_file" ]; then
  if [[ -d "$HOME/.web_package" ]]; then
    ls "$HOME/.web_package"
  fi
  end "`tput setaf 1`$wp_info_file`tput op` not found. Have you created your Web Package yet?"
fi

##
 # Show Info if requested
 #
if [ "$1" == 'info' ] || [ "$1" == 'i' ]; then
  cat $wp_info_file
  exit
fi

##
 # Show Version if requested
 #
if [ "$1" == 'version' ] || [ "$1" == 'v' ]; then
  get_version
  end 'Version: '$get_version_return;
fi

##
 # Show test output
 #
if [ "$1" == 'test' ]; then
  do_test $2 $3 $4 $5 $6 $7 $8
  end 'End of test.'
fi

##
 # Show Name if requested
 #
if [ "$1" == 'name' ] || [ "$1" == 'n' ]; then
  get_name
  end 'Name: '$get_name_return;
fi




##
 # Anything below here needs to have .web_package
 #

if [ ! -d ".web_package" ]; then
  end "`tput setaf 1`.web_package`tput op` directory not found. Try 'bump init'..."
fi

#
# bump build
# 
if [[ "$1" == 'build' ]]; then
  get_version
  # $2 would be a target script
  do_scripts $wp_build $get_version_return $get_version_return "$2"
  end "`tput setaf 2`Build complete.`tput op`"
fi

#
# bump unbuild
# 
if [[ "$1" == 'unbuild' ]]; then
  get_version
  do_scripts $wp_unbuild $get_version_return $get_version_return "$2"
  end "`tput setaf 2`Un-build complete.`tput op`"
fi


#
# bump dev
# 
if [[ "$1" == 'dev' ]]; then
  get_version
  do_scripts $wp_dev $get_version_return $get_version_return "$2"
  end "`tput setaf 2`Dev mode has been enabled.`tput op`"
fi

#
# bump update
# 
if [[ "$1" == 'update' ]]; then
  do_update
  end "`tput setaf 2`Update complete.`tput op`"
fi

#
# Checks fi update needs to be run
# 
do_check_update_needed

##
 # Merge (develop and master), delete branch, create tag
 #
if [ "$1" == 'done' ]; then
  do_done
fi

##
 # Explode shortcuts
 #
if [ "$1" == 'hotfix' ]; then
  severity='patch'
  release_type=$1
elif [ "$1" == 'release' ]; then
  severity='minor'
  release_type=$1
else
  severity=$1
  release_type=$2
fi

# Test to see if we can do this operation from this branch
allow=true

is_master_branch
if [ "$release_type" == 'hotfix' ] && [ $is_master_branch_return == false ]; then
  allow=false
  from_branch=master
fi

is_develop_branch
if [ "$release_type" == 'release' ] && [ $is_develop_branch_return == false ]; then
  allow=false
  from_branch=develop
fi

if [ "$allow" == false ]; then
  get_branch
  echo "current branch: `tput setaf 1`$get_branch_return`tput op` is not defined as a \"$from_branch\" branch."
  end "To execute a $release_type you must be on a \"$from_branch\" branch. Switch and try again."
fi

##
 # Prompt if invalid input
 #
if [ "$severity" != 'major' ] && [ "$severity" != 'minor' ] && [ "$severity" != 'patch' ] && [ "$severity" != 'alpha' ] && [ "$severity" != 'beta' ] && [ "$severity" != 'rc' ]; then
  echo
  echo 'Web Package Version Bump'
  echo '--------------------'
  echo "Arg 1 is one of: major, minor, patch, hotfix*, release*, alpha, beta, rc"
  echo "Arg 2 is one of: hotfix*, release*"
  echo
  echo "Arg 1 can also be: init, config, name(n), version(v), info(i), test, build, unbuild, dev, update"
  echo
  echo "*Workflow with Git:"
  echo "1. bump hotfix || bump release"
  echo "2. make the changes to your package and commit them"
  echo "3. bump done"
  exit
fi

##
 # OKAY FOLKS, WHY WE ALL CAME HERE; LET'S BUMP!
 #
get_version
version=$get_version_return
previous=$version

# Increment the version based on $severity level
increment_version $version $severity
version=$increment_version_return

if [ $previous == $version ]; then
  echo "Version unchanged: $previous ---> `tput setaf 1`$version`tput op`";
else
  echo "Version bumped: $previous ---> `tput setaf 2`$version`tput op`";
  
  # Update the file with the new version string
  sed -i.bak "s/version *= *\"*${previous}\"*/version = \"$version\"/1" $wp_info_file
  rm $wp_info_file.bak  

  # Lookfor build scripts and call
  if do_scripts $wp_build $previous $version; then
    # Pause to allow for processing
    if [[ "$wp_pause" -lt 0 ]]; then  
      read -n1 -p "`tput setaf 3`Press any key to proceed...`tput op`"
      echo
    elif [[ "$wp_pause" -gt 0 ]]; then
      echo "`tput setaf 2`Waiting for $wp_pause seconds...`tput op`"
      sleep $wp_pause
    fi
  fi
fi

# Git Integration...
if [ "$release_type" == 'hotfix' ] || [ "$release_type" == 'release' ]; then
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
  for branch in "${branches[@]}"
  do
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
  git co -b "$release_type-$get_version_with_prefix_return"
  git add -u
  git ci -m "Version bumped from $previous to $version"
fi
