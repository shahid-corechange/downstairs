#! /bin/ash

echo "Starting SSH ..."
/usr/sbin/sshd

if [[ "$APP_TYPE" == "app" ]]; then
  php artisan config:cache
  php artisan migrate --force
  php artisan permissions:sync --all
  php artisan global-settings:sync
  php artisan table:cache


  # Update process manager settings of PHP-FPM pool configuration
  sed -i "s/pm.max_children = .*/pm.max_children = $PHP_FPM_MAX_CHILDREN/" /usr/local/etc/php-fpm.d/www.conf
  sed -i "s/pm.start_servers = .*/pm.start_servers = $PHP_FPM_START_SERVERS/" /usr/local/etc/php-fpm.d/www.conf
  sed -i "s/pm.min_spare_servers = .*/pm.min_spare_servers = $PHP_FPM_MIN_SPARE_SERVERS/" /usr/local/etc/php-fpm.d/www.conf
  sed -i "s/pm.max_spare_servers = .*/pm.max_spare_servers = $PHP_FPM_MAX_SPARE_SERVERS/" /usr/local/etc/php-fpm.d/www.conf

  sed -i "s/^memory_limit = .*/memory_limit = $PHP_MEMORY_LIMIT/" /usr/local/etc/php/php.ini

  php-fpm
elif [[ "$APP_TYPE" == "queue-worker" ]]; then
  exec /usr/bin/supervisord -n -c /etc/supervisor/supervisord.conf
fi
