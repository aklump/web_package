#!/usr/bin/env bash

#
# @file
# Automatically add certain generated files to git during build.
# Web Package will add files changed during build, which were previously added,
# however it will not add new files never added.  Therefore this can be used to
# add the website folder of generated docs, for example.
#
git=$(type git >/dev/null 2>&1 && which git)
if [ "$git" ]; then
    # Note to support symlinks, we should cd first (per git).
    (cd $7/docs/public_html && git add .)
    (cd $7/help && git add .)
    (cd $7 git add README.txt)
    (cd $7 git add README.md)
    (cd $7 && git add composer.lock)
fi
