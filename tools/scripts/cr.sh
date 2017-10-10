#!/bin/bash


if [ ! -d "provision/local" ]; then
  echo "You must run it from the project root directory!"
  exit
fi

cd provision/local
echo "Clearing cache"
docker-compose exec php /bin/bash -c "cd docroot && drush cr"
cd - > /dev/null
