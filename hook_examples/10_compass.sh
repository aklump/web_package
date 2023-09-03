#!/usr/bin/env bash
prod_compass=$(type compass >/dev/null &2>&1 && which compass)
cd $7 && $prod_compass compile -e production --force --output-style compressed
