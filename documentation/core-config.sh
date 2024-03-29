#!/bin/bash
#
# @file
# Configuration

##
 # An array of output formats to disable, if any
 #
disabled = "drupal html text mediawiki doxygene"

##
 # File path to the php you want to use for compiling
 #
php = $(which php)
#php = '/Applications/MAMP/bin/php/php5.3.14/bin/php'

##
 # Lynx is required for output of .txt files
 #
lynx = $(which lynx)

##
 # The drupal credentials for a user who can access your iframe content
 #
#credentials = "http://user:pass@www.my-site.com/user/login";

##
 # The name of the drupal module to build advanced help output for, if
 # applicable
 #
#drupal_module = 'my_module';

##
 # The location of the advanced help output; this location is used in place of
 # the default, if enabled.
 #
#drupal_dir = '../help'

##
 # The file path to an extra README.txt file; when README.md is compiled and
 # this variable is set, the .txt version will be copied to this location.


# This would also copy README.md as well as README.txt to the directory one
# level above /source
README = '../README.md'
CHANGELOG = '../CHANGELOG.md'

#root_dir      = ""

#
# Defines pre/post hooks, shell or php scripts to call, space separated.
#
pre_hooks = "build-cs.php"
#post_hooks = "post_compile.sh post_compile.php"

version_file = "../composer.json"

#
# These paths are relative to the directory containing core-config.sh.
website_dir = '../docs'
