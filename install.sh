#!/usr/bin/env zsh
set -xe

# get directory for mediawiki

echo "$1"

if [ ! -d "$1" ]; then
  echo "Directory $1 does not exist."
  exit 1
fi

# verify that this directory contains LocalSettings.php

if [ ! -f "$1/LocalSettings.php" ]; then
  echo "Directory $1 does not contain LocalSettings.php."
  exit 1
fi

# TODO: check if there are changes to the DefaultLocalSettings.php?
ln -fv "DefaultLocalSettings.php" "$1/DefaultLocalSettings.php"
ln -fv "favicon-client.ico" "$1/favicon-client.ico"
ln -fv "favicon-repo.ico" "$1/favicon-repo.ico"

ln -fvsr "$PWD/createWikis.sh" "$1/createWikis.sh"
