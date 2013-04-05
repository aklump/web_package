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

##
 # BEGIN CONFIGURATION
 #

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
 # Values: major, minor, micro or no
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
if [ ! -f "$wP_info" ] && [ $(ls *.info 2>/dev/null) ]
then
  wp_info_file=$(ls *.info);
fi

##
 # The name of the project author
 #
wp_author=''

##
 # The inital version for new projects
 #
wp_init_version="0.0.1"

##
 # The default micro prefix
 #
wp_micro_prefix='.'

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
function load_config() {
  # Load user config
  parse_config $HOME/.web_package/config

  # Load project config
  parse_config .web_package/config
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

# Global variable
do_init_return='';

##
 # Initialize a new web_package directory
 #
 # @param string $1
 #   description of param
 #
 # @return NULL
 #   Sets the value of global $do_init_return
 #
function do_init() {
  if [ ! -d .web_package ]
  then
    mkdir .web_package
    touch .web_package/config
    echo "master = \"$wp_master\"" >> .web_package/config
    echo "develop = \"$wp_develop\"" >> .web_package/config
    echo "remote = $wp_remote" >> .web_package/config
    echo "create_tags = $wp_create_tags" >> .web_package/config
    echo "push_tags = $wp_push_tags" >> .web_package/config
    echo "push_master = $wp_push_master" >> .web_package/config
    echo "push_develop = $wp_push_develop" >> .web_package/config
    echo "micro_prefix = $wp_micro_prefix" >> .web_package/config
  fi

  # Create the info file
  if [ ! -f "$wp_info_file" ]
  then
    read -e -p "Enter package name: " name
    read -e -p "Enter package description: " description
    echo "name = $name" > $wp_info_file
    echo "description = $description" >> $wp_info_file
    echo "version = $wp_init_version" >> $wp_info_file
    # It may be that users don't want the author tag at all, so unless they
    # provide we will not write it to the .info file
    if [ "$wp_author" ]
    then
      echo "author = $wp_author" >> $wp_info_file
    fi

  fi

  get_name
  end "A new web_package \"$get_name_return\" (version: $wp_init_version) has been created."
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

  staged=$wp_micro_prefix;

  # standard versions
  echo 'MAJOR.MINOR.MICRO:'
  wp_micro_prefix='.'
  test_version 0.0.1 0.0.2 0.1 1.0 '0.1-alpha1' '0.1-beta1' '0.1-rc1'
  test_version "0.1" "0.1.1" "0.2" "1.0" "0.2-alpha1" "0.2-beta1" "0.2-rc1"
  test_version 1.0 1.0.1 1.1 2.0 '1.1-alpha1' '1.1-beta1' '1.1-rc1'
  test_version 1 1.0.1 1.1 2.0 '1.1-alpha1' '1.1-beta1' '1.1-rc1'
  test_version 9.9.9 9.9.10 9.10 10.0 "9.10-alpha9" "9.10-beta9" "9.10-rc9"
  test_version 99.99.99 99.99.100 99.100 100.0 "99.100-alpha99" "99.100-beta99" "99.100-rc99"

  echo 'MAJOR.MINOR(MINOR_PREFIX)MICRO:'
  wp_micro_prefix='-alpha'
  test_version "0.3${wp_micro_prefix}4" "0.3${wp_micro_prefix}5" "0.3" "1.0${wp_micro_prefix}1" "0.3${wp_micro_prefix}4" "0.4-beta1" "0.4-rc1"

  #todo Finish writing these alpha, beta, rc tests
  test_version 0.1 "0.2${wp_micro_prefix}1" "0.2" "1.0"
  test_version 1.0 "1.1${wp_micro_prefix}1" "1.1" "2.0"
  test_version 1 "1.1${wp_micro_prefix}1" "1.1" "2.0"
  #test_version 9.9.9 9.9.10 9.10 10.0
  #test_version 99.99.99 99.99.100 99.100 100.0

  # Drupal
  echo 'DRUPAL STYLE:'
  wp_micro_prefix='-alpha'
  test_version 7.x-1.0-alpha1 7.x-1.0-alpha2 7.x-1.0 7.x-2.0-alpha1
  test_version 7.x-1.0 7.x-1.1${wp_micro_prefix}1 7.x-1.1 7.x-2.0

  echo 'DRUPAL STYLE:'
  wp_micro_prefix='-beta'
  test_version 7.x-1.0-alpha1 7.x-1.0-alpha2 7.x-1.0 7.x-2.0-alpha1
  test_version 7.x-1.0 7.x-1.1${wp_micro_prefix}1 7.x-1.1 7.x-2.0

  echo 'DRUPAL STYLE:'
  wp_micro_prefix='-rc'
  test_version 7.x-1.0-alpha1 7.x-1.0-alpha2 7.x-1.0 7.x-2.0-alpha1
  test_version 7.x-1.0 7.x-1.1${wp_micro_prefix}1 7.x-1.1 7.x-2.0

  # source: http://drupal.org/node/1015226
  test_version 7.0 7.1${wp_micro_prefix}1 7.1 8.0
  test_version 8.0-beta1 8.0-beta2 8.0 9.0-beta1
  test_version 7.x-2.3 7.x-2.4${wp_micro_prefix}1 7.x-2.4 7.x-3.0
  test_version 8.x-2.0-alpha6 8.x-2.0-alpha7 8.x-2.0 8.x-3.0-alpha1

  #@todo These need work
  #_p='my.pre_fix1-'
  #_mp='mi.cro_prefix-'
  #
  #echo ODD PREFIX:
  #test_version ${_p}0.0.1 ${_p}0.0.2 ${_p}0.1 ${_p}1.0
  #test_version ${_p}0.1 ${_p}0.1.1 ${_p}0.2 ${_p}2.0
  #test_version ${_p}1 ${_p}1.0.1 ${_p}1.1 ${_p}2.0
  #
  #echo ODD MICRO_PREFIX:
  #test_version 1.0${_mp}1 1.0${_mp}2 1.0 2.0
  #
  #echo ODD PREFIX AND MICRO_PREFIX
  #test_version ${_p}0.0.1${_mp}
  #test_version ${_p}0.1${_mp}
  #test_version ${_p}1${_mp}

  wp_micro_prefix=$staged

}

##
 # A test for a single version number
 #
 # @param string $1
 #   The version to test
 # @param string $2
 #   The expected micro result
 # @param string $3
 #   The expected minor result
 # @param string $4
 #   The expected major result
 #
 # @return NULL
 #
function test_version() {
  success="`tput setaf 2` [OK]`tput op`"

  increment_version $1 micro
  result=''
  if [ "$3" ]
  then
    result=$success
  fi
  if [ "$2" ] && [ "$increment_version_return" != "$2" ]
  then
    result="`tput setaf 1` != $2 [FAIL]`tput op`"
  fi

  echo "micro: $1 --> $increment_version_return $result"

  increment_version $1 minor
  result=''
  if [ "$3" ]
  then
    result=$success
  fi
  if [ "$3" ] && [ "$increment_version_return" != "$3" ]
  then
    result="`tput setaf 1` != $3 [FAIL]`tput op`"
  fi
  echo "minor: $1 --> $increment_version_return $result"

  increment_version $1 major
  result=''
  if [ "$4" ]
  then
    result=$success
  fi
  if [ "$4" ] && [ "$increment_version_return" != "$4" ]
  then
    result="`tput setaf 1` != $4 [FAIL]`tput op`"
  fi
  echo "major: $1 --> $increment_version_return $result"

  increment_version $1 alpha
  result=''
  if [ "$5" ]
  then
    result=$success
  fi
  if [ "$5" ] && [ "$increment_version_return" != "$5" ]
  then
    result="`tput setaf 1` != $5 [FAIL]`tput op`"
  fi
  echo "alpha: $1 --> $increment_version_return $result"

  increment_version $1 beta
  result=''
  if [ "$6" ]
  then
    result=$success
  fi
  if [ "$6" ] && [ "$increment_version_return" != "$6" ]
  then
    result="`tput setaf 1` != $6 [FAIL]`tput op`"
  fi
  echo "beta: $1 --> $increment_version_return $result"

  increment_version $1 rc
  result=''
  if [ "$7" ]
  then
    result=$success
  fi
  if [ "$7" ] && [ "$increment_version_return" != "$7" ]
  then
    result="`tput setaf 1` != $7 [FAIL]`tput op`"
  fi
  printf "%-10s\n" "rc: $1 --> $increment_version_return $result"
  echo

  #final
  echo
}


#global variable
increment_version_return=''

###
 # Increment the version number
 #
 # This variable relies on a global: $version It should resemble this n.n.n, but
 # can also be n.n or just n, where n is any number
 #
 # @param string
 #   major: increments the first
 #   minor: increments the second
 #   ...: increments the third
 ##
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
    micro_prefix="${BASH_REMATCH[5]}"
    micro="${BASH_REMATCH[6]}"

  # 7.x-1.0
  elif [[ "$1" =~ $regex2 ]]
  then
    schema=2
    prefix="${BASH_REMATCH[1]}"
    major="${BASH_REMATCH[2]}"
    major_suffix="${BASH_REMATCH[3]}"
    minor="${BASH_REMATCH[4]}"
    micro_prefix=''
    micro=0

  # 7.x-1
  elif [[ "$1" =~ $regex3 ]]
  then
    schema=3
    prefix="${BASH_REMATCH[1]}"
    major="${BASH_REMATCH[2]}"
    major_suffix='.'
    minor=0
    micro_prefix=''
    micro=0
  else
    end "'$1' uses an unknown schema and cannot be incremented."
  fi

  case "$2" in
    major)
      if [ "$micro_prefix" == '.' ] || [ "$micro_prefix" = '' ]
      then
        major=$(($major + 1))
        major_suffix='.'
        minor=0
        micro_prefix=''
        micro=''
      else
        major=$(($major + 1))
        major_suffix='.'
        minor=0
        micro=1
      fi

      ;;
    minor)
      # Only increment minor if the prefix is '.' or prefix is empty
      if [ "$micro_prefix" == '.' ] || [ ! "$micro_prefix" ]
      then
        minor=$(($minor + 1))
      fi
      micro_prefix=''
      micro=''
      ;;
    micro)
      micro=$(($micro + 1))
      if [ ! "$micro_prefix" ]
      then
        micro_prefix=$wp_micro_prefix
        #if you bump micro and theres no prefix and the default is not . then
        #you increment minor too
        if [ $wp_micro_prefix != '.' ]
        then
          minor=$(($minor + 1))
        fi
      fi
      ;;
  esac



  if [ "$2" == 'alpha' ] || [ "$2" == 'beta' ] || [ "$2" == 'rc' ]
  then
    if [[ "$micro_prefix" == '.' ]] || [ ! "$micro_prefix" ] || ([[ "$micro_prefix" =~ [alpha] ]] && [ $2 == 'beta' ]) || ([[ "$micro_prefix" =~ [beta] ]] && [ $2 == 'rc' ])
    then
      if [ "$micro_prefix" != '.' ] || [ ! "$micro_prefix" ]
      then
        micro=1
      fi
      minor=$(($minor + 1))
      micro_prefix="-$2"
    fi
  fi

  increment_version_return="${prefix}${major}${major_suffix}${minor}${micro_prefix}${micro}"
  return;
}

# Global variable
get_branch_return='';

##
 # Return the name of the current git branch
 #
 # @return NULL
 #   Sets the value of global $get_branch_return
 #
function get_branch() {
  get_branch_return=$(git branch | sed -n -e 's/^\* \(.*\)/\1/p')
}

# Global variable
is_master_branch_return='';

##
 # Check if we're on a master branch
 #
 # @return NULL
 #   Sets the value of global $is_master_branch_return to true or false
 #
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

# Global variable
is_develop_branch_return='';

##
 # Check if we're on a master branch
 #
 # @return NULL
 #   Sets the value of global $is_develop_branch_return to true or false
 #
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

# Global variable
get_version_return='';

##
 # Return the current version
 #
 # @return NULL
 #   Sets the value of global $get_version_return
 #
function get_version() {
  get_version_with_prefix
  get_version_return=$get_version_with_prefix_return
}

get_version_with_prefix_return='';

##
 # Return the current version
 #
 # @return NULL
 #   Sets the value of global $get_version_return
 #
function get_version_with_prefix() {
  get_version_with_prefix_return=$(grep "version" $wp_info_file | cut -f2 -d "=" | sed -e 's/^ *//g' -e 's/ *$//g');
}

# Global variable
get_name_return='';

##
 # Return the current version
 #
 # @return NULL
 #   Sets the value of global $get_version_return
 #
function get_name() {
  get_name_return=$(grep "name" $wp_info_file | cut -f2 -d "=" | sed -e 's/^ *//g' -e 's/ *$//g');
}

# Global variable
storage_return='';

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
function storage() {
  file=".web_package/$1.txt"
  if [ "$2" ]
  then
    echo $2 > $file
  fi
  storage_return=`cat $file`
}


# Global variable
do_done_return='';

##
 # Do the done op
 #
 # @return NULL
 #   Sets the value of global $do_done_return
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
  if [ "$develop" ] && [ "$develop" != "$master" ]
  then
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
  if [ "$master" ]
  then
    git co "$master"
    git merge --no-ff $get_branch_return -m "Merge branch '$get_branch_return'"

    # Delete the temp branch
    echo
    read -n1 -p "Delete $get_branch_return? (y/n) " a;
    echo
    if [ "$a" == 'y' ]
    then
      # Delete the hotfix or release branch
      git br -d $get_branch_return
    fi
  fi

  # Tag the new release if we are supposed to for this severity
  storage severity
  do_tag=false;
  if [ $wp_create_tags == 'micro' ]
  then
    do_tag=true
  fi
  if [ $wp_create_tags == 'major' ] && [ $storage_return == 'major' ]
  then
    do_tag=true
  fi
  if [ $wp_create_tags == 'minor' ]
  then
    if [ $storage_return == 'major' ] || [ $storage_return == 'minor' ]
    then
      do_tag=true
    fi
  fi

  if [ "$do_tag" == true ]
  then
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
  if [ "$develop" ] && [ "$wp_push_develop" == 'ask' ]
  then
    echo
    read -n1 -p "git push $wp_remote $develop? (y/n) " a;
    echo
  fi
  # Push the tag if auto or prompt was yes
  if [ "$a" == 'y' ] || [ "$wp_push_develop" == 'auto' ]
  then
    if [ "$wp_push_develop" == 'auto' ]
    then
      echo
      echo "AUTO: git push $wp_remote $develop"
    fi
    git push $wp_remote $develop
  fi

  # Ask to push the master branch to origin?
  a=''
  if [ "$master" ] && [ "$wp_push_master" == 'ask' ]
  then
    echo
    read -n1 -p "git push $wp_remote $master? (y/n) " a;
    echo
  fi
  # Push the tag if auto or prompt was yes
  if [ "$a" == 'y' ] || [ "$wp_push_master" == 'auto' ]
  then
    if [ "$wp_push_master" == 'auto' ]
    then
      echo
      echo "AUTO: git push $wp_remote $master"
    fi
    git push $wp_remote $master
  fi

  # return to the original branch if not yet there
  get_branch
  if [ "$get_branch_return" != "$original_branch" ]
  then
    git co $original_branch
  fi
  end "Welcome back to $original_branch!"
}

##
 # Show Info if requested
 #
if [ "$1" == 'config' ]
then
  echo "master = $wp_master"
  echo "develop = $wp_develop"
  echo "remote = $wp_remote"
  echo "create_tags = $wp_create_tags"
  echo "push_tags = $wp_push_tags"
  echo "push_develop = $wp_push_develop"
  echo "push_master = $wp_push_master"
  echo "info_file = $wp_info_file"
  echo "micro_prefix = $wp_micro_prefix"
  exit
fi

##
 # Show Version if requested
 #
if [ "$1" == 'init' ]
then
  do_init
fi





##
 # Anything below here must have a .info file
 #

if [ ! -f "$wp_info_file" ]
then
  end "$wp_info_file not found. Have you created your Web Package yet?"
fi

##
 # Show Info if requested
 #
if [ "$1" == 'info' ] || [ "$1" == 'i' ]
then
  cat $wp_info_file
  exit
fi

##
 # Show Version if requested
 #
if [ "$1" == 'version' ] || [ "$1" == 'v' ]
then
  get_version
  end 'Version: '$get_version_return;
fi

##
 # Show test output
 #
if [ $1 == 'test' ]
then
  do_test $2 $3 $4 $5 $6 $7 $8
  end 'End of test.'
fi

##
 # Show Name if requested
 #
if [ "$1" == 'name' ] || [ "$1" == 'n' ]
then
  get_name
  end 'Name: '$get_name_return;
fi

##
 # Merge (develop and master), delete branch, create tag
 #
if [ "$1" == 'done' ]
then
  do_done
fi

##
 # Explode shortcuts
 #
if [ "$1" == 'hotfix' ]
then
  severity='micro'
  release_type=$1
elif [ "$1" == 'release' ]
then
  severity='minor'
  release_type=$1
else
  severity=$1
  release_type=$2
fi

# Test to see if we can do this operation from this branch
allow=true

is_master_branch
if [ "$release_type" == 'hotfix' ] && [ $is_master_branch_return == false ]
then
  allow=false
  from_branch=master
fi

is_develop_branch
if [ "$release_type" == 'release' ] && [ $is_develop_branch_return == false ]
then
  allow=false
  from_branch=develop
fi

if [ "$allow" == false ]
then
  get_branch
  echo "current branch: $get_branch_return is not defined as a \"$from_branch\" branch."
  end "To execute a $release_type you must be on a \"$from_branch\" branch. Switch and try again."
fi

##
 # Prompt if invalid input
 #
if [ "$severity" != 'major' ] && [ "$severity" != 'minor' ] && [ "$severity" != 'micro' ] && [ "$severity" != 'alpha' ] && [ "$severity" != 'beta' ] && [ "$severity" != 'rc' ]
then
  echo
  echo 'Web Package Version Bump'
  echo '--------------------'
  echo "Arg 1 is one of: major, minor, micro, hotfix*, release*, alpha, beta, rc"
  echo "Arg 2 is one of: hotfix*, release*"
  echo
  echo "Arg 1 can also be: init, config, name(n), version(v), info(i), test"
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
previous=$(grep "version" $wp_info_file | cut -f2 -d "=");

# Increment the version based on $severity level
increment_version $version $severity
version=$increment_version_return

if [ $previous == $version ]
then
  echo "Version unchanged: $previous ---> `tput setaf 1`$version`tput op`";
else
  echo "Version bumped: $previous ---> `tput setaf 2`$version`tput op`";
fi



# Update the file with the new version string
sed -i.bak "s/version *= *${previous}/version = $version/1" $wp_info_file
rm $wp_info_file.bak

# Git Integration...
if [ "$release_type" == 'hotfix' ] || [ "$release_type" == 'release' ]
then
  # Store this branch so we can return to it when done
  get_branch
  storage return $get_branch_return
  storage severity $severity

  # Make note of the correct master/develop branches of this context
  is_master_branch
  is_develop_branch
  if [ "$is_master_branch_return" == true ]
  then
    branches=($wp_master)
  elif [ "$is_develop_branch_return" == true ]
  then
    branches=($wp_develop)
  fi

  # Locate this index and then match
  i=0
  for branch in "${branches[@]}"
  do
    if [ "$branch" == "$get_branch_return" ]
    then
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
  git add $wp_info_file
  git ci -m "Version bumped from $previous to $version"
fi
