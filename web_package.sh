#!/bin/bash

#
# @file
# Increments filename version and creates new branch
#
# USAGE:
#   The script is called with one or two arguments. The first argument is one
#   of: 'major', 'minor' or 'micro'. It denotes which part of the version string
#   to increment. The are special values for this argument discussed below.
#
#   The second argument is the prefix to the branch name. Examples are 'hotfix',
#   and 'release'
#
#   As promised there are special values for the first argument. The first of
#   these is 'hotfix'. Pass this as argument one and it is equivalent to the
#   following: . bump.sh micro hotfix So it saves you typing 'micro' The other
#   special value is 'release', which is equivalent to the following: .
#   git-loft.sh minor release.
#
#   In the event that you are releasing a new major release you would need to
#   call . bump.sh major release
#
#   When you are finished with your hotfix or release then use 'bump done' and
#   the temporary branch will be merged back into develop and master, deleted
#   and then a tag will be created with the new version number
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
  declare -a part=( ${version//\./ } )
  if [ ! "${part[0]}" ]
  then
    part[0]=0
  fi
  if [ ! "${part[1]}" ]
  then
    part[1]=0
  fi
  if [ ! "${part[2]}" ]
  then
    part[2]=0
  fi
  case $1 in
     major)
       part[0]=$((${part[0]} + 1))
       part[1]=0
       part[2]=0
       ;;
     minor)
       part[1]=$((${part[1]} + 1))
       part[2]=0
       ;;
     *)
       part[2]=$((${part[2]} + 1));;
  esac
  version="${part[0]}.${part[1]}.${part[2]}"
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
get_version_return='';

##
 # Return the current version
 #
 # @return NULL
 #   Sets the value of global $get_version_return
 #
function get_version() {
  get_version_return=$(grep "version" $filename | cut -f2 -d "=");
}


# Global variable
merge_return='';

##
 # The name of the file that holds your version string
 #
filename='web_package.info';

##
 # Show Info if requested
 #
if [ "$1" == 'info' ] || [ "$1" == '-i' ]
then
  cat *.info;
  exit;
fi

##
 # Merge (develop and master), delete branch, create tag
 #
if [ "$1" = 'done' ]
then
  get_branch

  # Try to merge into develop
  echo "Merging into develop..."
  git co develop
  git merge --no-ff $get_branch_return -m "Merge branch '$get_branch_return'"

  # Try to merge into master
  read -n1 -p "Continue to master? (y/n) " a;
  echo
  if [ "$a" != 'y' ]
  then
    echo 'CANCELLED!'
    git co $get_branch_return
    exit
  fi
  git co master
  git merge --no-ff $get_branch_return -m "Merge branch '$get_branch_return'"

  # Delete the temp branch
  read -n1 -p "Delete $get_branch_return? (y/n) " a;
  echo
  if [ "$a" != 'y' ]
  then
    echo 'CANCELLED!'
    git co $get_branch_return
    exit
  fi

  if [ "$get_branch_return" != 'master' ] && [ "$get_branch_return" != 'develop' ]
  then
    git br -d $get_branch_return
  fi

  # Tag the new release
  get_version
  git tag $get_version_return
  exit;
fi

##
 # Explode shortcuts
 #
if [ "$1" == 'hotfix' ]
then
  severity='micro'
  branch='hotfix'
  from_branch='master'
elif [ "$1" == 'release' ]
then
  severity='minor'
  branch='release'
  from_branch='develop'
else
  severity=$1
  branch=$2
fi

if [ "$branch" != 'hotfix' ] && [ "$branch" != 'release' ]
then
  branch=''
fi

if [ "$from_branch" ]
then
  get_branch
  if [ "$get_branch_return" ] && [ "$from_branch" != "$get_branch_return" ]
  then
    echo "current branch: $get_branch_return"
    echo "To execute a $branch you must be on the $from_branch branch. Switch and try again."
    exit;
  fi
fi

##
 # Prompt if invalid input
 #
if [ ! "$severity" ]
then
  echo
  echo 'Web Package Version Bump'
  echo '--------------------'
  echo "Arg 1 is one of: major, minor, micro, hotfix*, release*, or info (-i)"
  echo "Arg 2 is one of: hotfix*, release*"
  echo
  echo "*Workflow with Git:"
  echo "1. bump hotfix || bump release"
  echo "2. make the changes to your package and commit them"
  echo "3. bump done"
  exit
fi

##
 # Check for version file
 #
if [ ! -f "$filename" ]
then
  echo "$filename not found.  Create Y/N?"
  read -sn1 CONFIRM
  if [ $CONFIRM != 'Y' ] && [ $CONFIRM != 'y' ]
  then
    echo 'Operation cancelled.'
    exit
  fi
  read -e -p "Enter package name: " name
  read -e -p "Enter package description: " description
  echo "name = $name" > $filename
  echo "description = $description" >> $filename
  echo 'version = 0.0.0' >> $filename
  echo "$filename file was created."
fi

get_version
version=$get_version_return
previous=$version;

# Increment the version based on $branch
increment_version $severity
echo "Version bumped: $previous ---> $version";

# Replace in file
sed -i.bak "s/version *= *${previous}/version = $version/1" $filename
rm $filename.bak

# Checkout a new branch and commit $filename with new version
if [ "$branch" ]
then
  git co -b "$branch-$version"
  git add $filename
  git ci -m "Version bumped from $previous to $version"
fi
