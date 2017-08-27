<?php

/**
 * @file
 * Drupal site-specific configuration file.
 */

$databases = [];

// For separate DB.
$databases['default']['default'] = [
  'database' => 'DB_NAME',
  'username' => 'USER',
  'password' => 'PASSWORD',
  'prefix' => '',
  'host' => 'mariadb',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
];
