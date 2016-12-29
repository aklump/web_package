#!/usr/bin/env bash
npm=$(type npm >/dev/null 2>&1 && which npm);
$npm run build;
