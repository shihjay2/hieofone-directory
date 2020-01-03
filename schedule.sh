#!/bin/bash
# Run scheduler
while [ true ]
do
  php /var/www/directory/artisan schedule:run
  sleep 60
done
