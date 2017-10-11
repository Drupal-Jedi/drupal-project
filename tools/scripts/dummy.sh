#!/bin/bash

bash ./tools/scripts/before-install.sh travis_test
cp provision/local/docker-runtime/settings/travis_test-settings.inc docroot/sites/default/travis_test-settings.inc

echo "require DRUPAL_ROOT . '/sites/default/travis_test-settings.inc';" >> docroot/sites/prj-settings.inc
sed -i 's/mariadb/127.0.0.1/g' docroot/sites/default/travis_test-settings.inc

cd docroot
../vendor/bin/drush site-install standard --account-name=admin --account-pass=admin --config-dir=../config --site-name=DrupalJedi -y
