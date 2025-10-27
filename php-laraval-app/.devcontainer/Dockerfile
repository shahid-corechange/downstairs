FROM mcr.microsoft.com/devcontainers/base:ubuntu-22.04

ARG PHP_VERSION=8.1 \
    NODE_VERSION=18.16.0

ENV DEBIAN_FRONTEND=noninteractive \
    NVM_DIR=/home/vscode/.nvm

RUN apt update && apt upgrade -y \
    && apt install -y \
        apt-transport-https ca-certificates \
        curl git software-properties-common

RUN add-apt-repository ppa:ondrej/php \
    && apt update \
    && apt install -y \
        php${PHP_VERSION}-cli php${PHP_VERSION}-common \
        php${PHP_VERSION}-mysql php${PHP_VERSION}-zip \
        php${PHP_VERSION}-gd php${PHP_VERSION}-mbstring \
        php${PHP_VERSION}-curl php${PHP_VERSION}-xml \
        php${PHP_VERSION}-bcmath php${PHP_VERSION}-fpm \
        php${PHP_VERSION}-intl php${PHP_VERSION}-redis \
        php${PHP_VERSION}-sqlite3 php${PHP_VERSION}-pcov composer

RUN curl -fsSL https://download.docker.com/linux/ubuntu/gpg | \
        gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg \
    && echo \
        "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable" | \
        tee /etc/apt/sources.list.d/docker.list > /dev/null \
    && apt update \
    && apt install -y docker-ce \
    && usermod -aG docker vscode

USER vscode

RUN curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.3/install.sh | bash \
    && . $NVM_DIR/nvm.sh \
    && nvm install ${NODE_VERSION} \
    && npm i -g yarn
