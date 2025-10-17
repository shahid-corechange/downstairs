FROM nginx:stable-alpine3.17-slim

RUN apk update \
    && apk upgrade \
    && apk add --no-cache bash=5.2.15-r0 logrotate=3.20.1-r3 openssh=9.1_p1-r6

COPY docker/nginx/sshd_config /etc/ssh/
RUN echo 'root:Docker!' | chpasswd \
    && cd /etc/ssh/ \
    && ssh-keygen -A

COPY docker/nginx/logrotate.conf /etc/logrotate.d/nginx
RUN touch /var/log/messages

COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx/sites-available /etc/nginx/sites-available
COPY docker/nginx/sites-enabled /etc/nginx/sites-enabled
COPY public /var/www/html/public

COPY docker/nginx/entrypoint.sh entrypoint.sh

CMD [ "/bin/ash", "entrypoint.sh" ]

EXPOSE 80 2222
