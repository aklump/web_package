#!/usr/bin/env bash
source "$LOBSTER_APP_ROOT/lib/lobster/dist/bootstrap.sh"

# Keep this reassign here to allow app bootstrap to modify the arguments before we assign the op.
lobster_op=${lobster_args[0]}

lobster_include 'functions'
lobster_include 'init'
source "$LOBSTER_APP_ROOT/lib/lobster/dist/router.sh"
