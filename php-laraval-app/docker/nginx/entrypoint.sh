#! /bin/ash

echo "Starting SSH ..."
/usr/sbin/sshd

nginx -g "daemon off;"
