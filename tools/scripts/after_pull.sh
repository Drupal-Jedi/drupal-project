#!/bin/bash

# Script execute all necessary commands after each pull.

if [ ! -d "provision/local" ]; then
  echo "You must run it from the project root directory!"
  exit
fi

cd provision/local
echo "1/4 Installing new composer and npm packages. Building css."
docker-compose exec php /bin/bash -c "cd docroot && composer install"
echo "2/4 Importing new configuration"
docker-compose exec php /bin/bash -c "cd docroot && drush cim -y" > /dev/null
echo "3/4 Applying updates to current database"
docker-compose exec php /bin/bash -c "cd docroot && drush updb --entity-updates -y" > /dev/null
echo "4/4 Clearing cache"
docker-compose exec php /bin/bash -c "cd docroot && drush cr" > /dev/null
cd - > /dev/null
