#!/usr/bin/env bash
lobster_access has_info_file

if ! [ "${lobster_args[1]}" ]; then
  get_info_string
  lobster_notice "$get_info_string_return"
  lobster_echo
else
  get_info_string "${lobster_args[1]}"
  lobster_notice "$get_info_string_return"
fi
