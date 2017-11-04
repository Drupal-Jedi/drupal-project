#!/bin/bash

if [ ! -f docroot/index.php ] ; then
  composer drupal-scaffold
fi

