#!/bin/bash
#
# @file Compiles Loft Docs.
# 
cd "$7/docs/" && ./core/compile.sh

# Now we need to replace some entities for the drupal page
perl=$(type  >/dev/null 2>&1 && which perl)
$perl -pi -e 's/&gt;/>/g' "$7/help/drupal-org.html"
