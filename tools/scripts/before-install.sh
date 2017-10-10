#!/bin/bash

if [ ! -d "docroot" ]; then
  echo "You must run it from the project root directory!"
  exit 1
fi

if [ -z "$1" ]; then
  echo "You must pass project name as argument to the script"
  exit 1
fi

project_name=$1

sed -i 's/project_name/'"$project_name"'/g' docroot/sites/default/settings.php
sed -i 's/project_name/'"$project_name"'/g' docroot/sites/prj-settings.inc
sed -i 's/project_name/'"$project_name"'/g' provision/local/docker-compose.yml
sed -i 's/project_name/'"$project_name"'/g' provision/dev/docker-compose.yml
sed -i 's/project_name/'"$project_name"'/g' provision/stage/docker-compose.yml

mkdir provision/local/docker-runtime
mkdir provision/local/docker-runtime/settings

cp provision/local/example.project_name-settings.inc provision/local/docker-runtime/settings/"$project_name"-settings.inc
