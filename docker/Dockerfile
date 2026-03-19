FROM php:8.4-cli

RUN apt-get update && apt-get install -y git libzip-dev make nano zip zlib1g-dev \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN groupadd -g 1000 jsonld && \
    useradd -m -u 1000 -g jsonld -G www-data -s /bin/bash jsonld

# Helper script to avoid git warning about different owners of the project directory.
COPY ./entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

WORKDIR /home/jsonld/code

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
