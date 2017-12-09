#!/bin/bash
# 
# @file
# Shows a config var's value

if [ ! "${lobster_args[2]}" ]; then
  lobster_error "Usage show config {varname}"
  lobster_exit 1
fi

eval value=\$${lobster_args[2]}
lobster_echo "\"${lobster_args[2]}\" is set to $(lobster_success $value)"
