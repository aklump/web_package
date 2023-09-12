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
# routes that have not been converted must be listed here.
if [[ "$1" == 'info' ]] || [[ "$1" == 'i' ]] || [[ "$1" == 'config' ]] || [[ "$1" == 'test' ]] || [[ "$1" == 'update' ]] || [[ "$1" == 'hooklib' ]]; then
  source "$LOBSTER_APP_ROOT/lib/lobster/dist/lobster.sh"
else
  php "$LOBSTER_APP_ROOT/web_package.php" $@
fi
