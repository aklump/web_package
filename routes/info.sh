#!/usr/bin/env bash
lobster_access has_info_file

if ! [ "${lobster_args[1]}" ]; then
    get_info_string
    lobster_notice "$get_info_string_return"
    lobster_echo
else
  case "${lobster_args[1]}" in
  'name')
      get_name
      lobster_notice "$get_name_return"
      ;;
  'version')
      get_version
      lobster_notice "$get_version_return"
      ;;
  esac
fi
