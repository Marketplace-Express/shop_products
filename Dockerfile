#
# Shop products service Dockerfile
# Copyrights to Wajdi Jurry <github.com/wajdijurry>
#
FROM php:7.3-fpm

LABEL maintainer="Wajdi Jurry<github.com/wajdijurry>"

# Important! To prevent this warning "debconf: unable to initialize frontend"
ARG DEBIAN_FRONTEND=noninteractive

# Update apt repos
RUN echo "Updating repos ..." && apt-get -y update > /dev/null
RUN apt-get install -yq apt-utils 2>&1 | grep -v "debconf: delaying package configuration, since apt-utils is not installed"

# Return working directory to its default state
WORKDIR /src

# Copy project files to container
RUN echo "Copying project files ..."
ADD . ./

# Install dependencies
RUN chmod +x ./utilities/install-dependencies.sh && ./utilities/install-dependencies.sh

# Install Extensions
RUN chmod +x ./utilities/install-extensions.sh && ./utilities/install-extensions.sh

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
# Install composer dependencies
RUN rm -rf app/vendor composer.lock && \
    composer clearcache && \
    composer install --ignore-platform-reqs
    
# Create symlink for phalcon bin
RUN ln -fs /src/app/vendor/bin/phalcon /usr/local/bin

ENTRYPOINT ["/bin/bash", "utilities/docker-entrypoint.sh"]