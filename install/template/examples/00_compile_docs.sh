#!/bin/bash
#
# @file Compiles Loft Docs.
#
git=$(type git >/dev/null 2>&1 && which git)
(cd "$7/docs/" && ./core/compile.sh)
(cd "$7" && $git add docs)
