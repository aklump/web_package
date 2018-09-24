#!/usr/bin/env bash
cd "$7" && composer dumpautoload --optimize || return 1
