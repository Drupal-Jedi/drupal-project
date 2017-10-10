#!/bin/bash

if [ ! -d "provision/local" ]; then
  echo "You must run it from the project root directory!"
  exit
fi

cd provision/local
echo "Generation login link"
docker-compose exec php /bin/bash -c "cd docroot && drush uli"
cd - > /dev/null
