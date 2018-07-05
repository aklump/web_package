#!/usr/bin/env bash
#
# @file Do a yarn update
#
# You should include yarn1.sh as well so the note prints before the delay begins.
yarn=$(type yarn >/dev/null 2>&1 && which yarn)
cd "$7" && $yarn
