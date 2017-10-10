#!/bin/bash

if [ ! -d "provision/local" ]; then
  echo "You must run it from the project root directory!"
  exit
fi

cd provision/local
docker-compose exec php /bin/bash -c "cd docroot && drush site-install standard --account-name=admin --account-pass=admin --config-dir=../config --site-name=DrupalJedi -y"
echo ""
echo "Use admin/admin credentials to login."
cd - > /dev/null
