# Use an official Ubuntu image as the base image
FROM ubuntu:20.04

# Set the time zone to America/Sao_Paulo
COPY timezone /etc/timezone
RUN ln -sf /usr/share/zoneinfo/$(cat /etc/timezone) /etc/localtime

# Install Apache, PHP, and the required modules
RUN apt-get update && apt-get install -y \
    apache2 \
    libapache2-mod-php \
    php \
    curl \
    php-curl \
    php-xml \
 && rm -rf /var/lib/apt/lists/*

# Copy the PHP code into the container
COPY src/ /var/www/html/

# Configure Apache to use PHP
RUN a2enmod php7.4

# Expose port 80 to the host
EXPOSE 80

# Start the Apache web server
CMD ["apachectl", "-D", "FOREGROUND"]