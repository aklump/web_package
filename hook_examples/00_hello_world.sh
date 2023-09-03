#!/usr/bin/env bash
# Writes a file called built.txt to a build directory `built/test/built.txt`
# with the contents "Hello World"
mkdir -p $7/dist/bash/test && echo "Hello World" > $7/dist/bash/test/built.txt

if test -f $7/dist/bash/test/built.txt; then
  echo "It worked! To see for yourself type: cat dist/bash/test/built.txt"
  echo "You should see the phrase: Hello World"
fi
