#!/usr/bin/env bash
version=${lobster_args[1]}

# Allow the passing of v to load in the current version
if [ "$version" == 'v' ]; then
  get_version
  version=$get_version_return
fi

do_test $version ${lobster_args[2]} ${lobster_args[3]} ${lobster_args[4]} ${lobster_args[5]} ${lobster_args[6]} ${lobster_args[7]}
lobster_success 'End of test.'
