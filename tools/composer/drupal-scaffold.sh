#!/bin/bash

if [ ! -f index.php ] ; then
  composer drupal-scaffold
fi

