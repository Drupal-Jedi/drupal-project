<?php

/**
 * @file
 * Drupal site-specific configuration file.
 */

$databases = [];

// For separate DB.
$databases['default']['default'] = [
  'database' => 'project_name',
  'username' => 'project_name',
  'password' => 'project_name',
  'prefix' => '',
  'host' => 'mariadb',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
];
