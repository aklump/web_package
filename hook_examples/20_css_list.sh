#!/bin/bash
#
# @file
#
# This file will compile a list of all declared css classes.
#
css_list=$(type css_list >/dev/null &2>&1 && which css_list)
if [ "$css_list" ]; then
    test -e "$7/assets" || mkdir -p "$7/assets"
    (cd "$7/dist" && ${css_list} . > "$7/assets/css_list.txt")
fi
