#!/bin/bash
#
# @file Compiles Loft Docs.
#
test ! -d "$7/docs" || rm -rf "$7/docs" || exit 1
cd "$7/documentation/" && ./core/compile.sh || exit 1
test -f "$7/docs/index.html" || exit 1

# Now we need to replace some entities for the drupal page
perl=$(type  >/dev/null 2>&1 && which perl)
$perl -pi -e 's/&gt;/>/g' "$7/help/drupal-org.html" || exit 1

exit 0
