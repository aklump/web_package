#!/usr/bin/env bash

#
# @file Update dependencies using yarn
#

yarn || hook_exception
composer update || hook_exception

