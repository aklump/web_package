#!/usr/bin/env bash
lobster_access has_info_file

if ! [ "${lobster_args[1]}" ]; then
  get_info_string
  echo "$get_info_string_return"
  lobster_echo
else
  get_info_string "${lobster_args[1]}"
  # Do not colorize this output. 2018-09-24T14:04, aklump
  echo "$get_info_string_return"
fi
