#!/usr/bin/env bash

#
# @file
# Runs Cypress tests in the build/ directory.
#
# 1. Place all tests to be run into /cypress/integration/build/
#
# @link https://docs.cypress.io/guides/guides/command-line.html#cypress-run
#

./node_modules/.bin/cypress run --spec "./cypress/integration/build/**/*" || build_fail_exception "Cypress testing failed."
