#!/bin/bash
#
# @file
# Provide functions to be used by example_app.  This file is
# auto-loaded every time example_app.sh is called, but after
# functions at the core level of Lobster.

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
  if [ $# -eq 1 ]; then
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

  # Handle the git root, or auto detect.
  if [ ! "$wp_git_root" ]; then
    wp_git_root=$(lobster_upfind .git && echo "$lobster_upfind_dir")
    if [ "$wp_git_root" ]; then
      wp_git_root=$(dirname $wp_git_root)
    fi
  fi

  # Legacy support convert micro to patch and alert user
  if [[ "$wp_create_tags" == 'micro' ]]; then
    wp_create_tags='patch';
    echo "`tput setaf 3`Replace 'create_tags = micro' with 'create_tags = patch' in .web_package/config`tput op`"
  fi

  # this will convert something like *.info to a filename.
  if ! test -e "$wp_info_file"; then
    for file in $(ls $wp_info_file 2>/dev/null); do
      wp_info_file=$file
      break;
    done
  fi

  # If there is still no file, then we will replace * with web_package
  if [ "$wp_info_file" ] && ! test -e "$wp_info_file" && [ "${wp_info_file:0:1}" == "*" ]; then
    wp_info_file="$lobster_app_name${wp_info_file:1}"
  fi
}

##
 # Parse a config file
 #
 # @param string $1
 #   The filepath of the config file
 #
function parse_config() {
  if [ -f $1 ]; then
    while read line; do
      if [[ "$line" =~ ^[^#[]+ ]]; then
        name=${line%% =*}
        value=${line##*= }
        if [ "$name" ]; then
          eval wp_$name=$value
        fi
      fi
    done < $1
  fi
}

# If .web_package/config then we can override defaults, but optional.
load_config

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
    local prev=$2
    local version=$3
    local script_filter=$4

    if [[ -z "$dir" ]] || [[ ! -d "$dir" ]]; then
        return 1
    fi

    get_name
    get_info_string 'description'
    local description=$get_info_string_return
    get_info_string 'homepage'
    local homepage=$get_info_string_return
    get_info_string 'author'
    local author=$get_info_string_return
    local date=$(date)

    # Check to see if a scriptname has been provided, instead.
    if [[ "$4" ]]; then
        lobster_echo "Looking for provided file: '$4'"
        target_scripts=$(ls "$dir/"*"$4"*)
    else
        local target_scripts=($(ls "$dir/"*))
    fi
    for file in ${target_scripts[@]}; do
      if ! test -e "$file"; then
        echo "`tty -s && tput setaf 1`wp error: $dir`tty -s && tput op`"
        echo "`tty -s && tput setaf 1`detected hook file: '$file' doesn't exist!`tty -s && tput op`"

      #skip files that begin with underscore
      elif [[ $basename == _* ]]; then
        lobster_warning "Skipping \"$basename\" because filename starts with _"
      else
        local script=$(basename $file)
        lobster_notice "Executing callback: $script..."
        # We change directory to make sure every script executes from $project_root.
        output="$(cd $project_root && $wp_php "$LOBSTER_APP_ROOT/includes/hook_runner.php" "$file" "$prev" "$version" "$get_name_return" "$description" "$homepage" "$author" "$project_root" "$date" "$project_root/$wp_info_file" "$dir" "$project_root/.web_package" "$LOBSTER_APP_ROOT" "$project_root/.web_package/hooks")"
        exit_status=$?
        [[ "$output" ]] && lobster_echo "$output"
        echo
        [[ $exit_status -ne 0 ]] && lobster_error "BUILD HAS FAILED!" && echo && lobster_exit
      fi
    done
    return 0
}

function needs_update() {
  if [[ ! -d "$project_root/.web_package/tmp" ]]; then
    return 0
  fi
  if [[ ! -d "$project_root/.web_package/hooks" ]]; then
    return 0
  fi
  return 1
}

##
 # Test all version bumping
 #
 # @return NULL
 #
function do_test() {
  if [ "$1" ]; then
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

  if [[ "$1" =~ $regex1 ]]; then
    schema=1
    prefix="${BASH_REMATCH[1]}"
    major="${BASH_REMATCH[2]}"
    major_suffix="${BASH_REMATCH[3]}"
    minor="${BASH_REMATCH[4]}"
    patch_prefix="${BASH_REMATCH[5]}"
    patch="${BASH_REMATCH[6]}"

  # 7.x-1.0
  elif [[ "$1" =~ $regex2 ]]; then
    schema=2
    prefix="${BASH_REMATCH[1]}"
    major="${BASH_REMATCH[2]}"
    major_suffix="${BASH_REMATCH[3]}"
    minor="${BASH_REMATCH[4]}"
    patch_prefix=''
    patch=0

  # 7.x-1
  elif [[ "$1" =~ $regex3 ]]; then
    schema=3
    prefix="${BASH_REMATCH[1]}"
    major="${BASH_REMATCH[2]}"
    major_suffix='.'
    minor=0
    patch_prefix=''
    patch=0
  else
    lobster_failed "'$1' is not a valid version pattern and cannot be incremented."
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

  if [ "$2" == 'alpha' ] || [ "$2" == 'beta' ] || [ "$2" == 'rc' ]; then

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
  get_info_string_return=$($wp_php $LOBSTER_APP_ROOT/plugins/$wp_plugin_parse/$wp_plugin_parse.php $LOBSTER_PWD/$wp_info_file "$1")
}

##
 # Put an info string to persistent storage
 #
 # @param string the info key
 # @param string the info value
 #
function put_info_string() {
  result=$($wp_php $LOBSTER_APP_ROOT/plugins/$wp_plugin_parse/$wp_plugin_parse.php $LOBSTER_PWD/$wp_info_file "$1" "$2")
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
  if [ "$2" ]; then
    echo $2 > "$file"
  fi
  storage_return=$(test -e "$file" && cat "$file")

}

function has_info_file () {
  if test -e "$wp_info_file"; then
    return 0
  fi
  lobster_warning "Please create '$wp_info_file' before attempting '$lobster_op'."
  return 1
}

function is_initialized () {
  if test -e "$LOBSTER_PWD/.web_package"; then
    return 0
  fi
  lobster_warning "Please call 'init' before attempting '$lobster_op'."
  return 1
}

function is_not_initialized () {
  if is_initialized > /dev/null ; then
    lobster_error "$LOBSTER_PWD is already initialized."
    return 1
  fi
  return 0
}

#
# Access callback for the bump op
#
function bump_access () {
  # Test to see if we can do this operation from this branch
  local allow=true
  local from_branch

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
    lobster_error "current branch: $get_branch_return is not defined as a '$from_branch' branch."
    lobster_error "To execute a $release_type you must be on a '$from_branch' branch. Switch and try again."
    return 1
  fi

  # See if the severity is implied
  declare -a array=("${lobster_args[1]}" "hotfix" "release")
  if lobster_in_array ${array[@]}; then
    severity="patch"
    release_type=${lobster_args[1]}
  else
    severity=${lobster_args[1]}
    release_type=${lobster_args[2]}
  fi

  # Validate the severity.
  declare -a array=("$severity" "major" "minor" "patch" "alpha" "beta" "rc")
  if ! lobster_in_array ${array[@]}; then
    lobster_error "Invalid severity $severity"
    return 1
  fi

  # Validate the type.
  declare -a array=("$release_type" "hotfix" "release")
  if [ "$release_type" ] && ! lobster_in_array ${array[@]}; then
    lobster_error "Invalid release type $release_type"
    return 1
  fi

}

function done_access() {
  # Make sure we don't try to run 'bump done' on a master or develop branch
  is_master_branch
  if [ $is_master_branch_return == true ]; then
    lobster_error 'You cannot finish a "master" branch.'
    return 1
  fi

  is_develop_branch
  if [ $is_develop_branch_return == true ]; then
    lobster_error 'You cannot finish a "develop" branch.'
    return 1
  fi

  return 0
}
