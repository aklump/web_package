#!/bin/bash
#
# @file
# Controller file for example_app.

LOBSTER_APP="${BASH_SOURCE[0]}"
while [ -h "$LOBSTER_APP" ]; do
  dir="$(cd -P "$(dirname "$LOBSTER_APP")" && pwd)"
  LOBSTER_APP="$(readlink "$LOBSTER_APP")"
  [[ $LOBSTER_APP != /* ]] && LOBSTER_APP="$dir/$LOBSTER_APP"
done
LOBSTER_APP_ROOT="$(cd -P "$(dirname "$LOBSTER_APP")" && pwd)"
lobster_core_verbose=0

# We're converting this from Lobster to Symfony console route by route.  All
# routes that should us symfony console should be added here.
if [[ "$1" == 'init' ]] || [[ "$1" == 'major' ]] || [[ "$1" == 'minor' ]] || [[ "$1" == 'patch' ]]; then
  php "$LOBSTER_APP_ROOT/web_package.php" $@
else
  source "$LOBSTER_APP_ROOT/lib/lobster/dist/lobster.sh"
fi
