Shop: Products Service
--
### Installation:

After starting a new container, go inside it and do the following:

1- Enable Apache2 rewrite module and HTTP Authorization:
```shell script
~# a2enmod rewrite
~# vim /etc/apache2/conf-enabled/security.conf
```
Add this line at the end of the file:
```shell script
SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1
```
Then, save and exit. After that, reload apache2 service:
```shell script
~# service apache2 reload
[ ok ] Reloading Apache httpd web server: apache2.
```

2- Install dependencies:
```text
Note: Type "php -m" and make sure these extensions are installed and enabled:
1. phalcon
2. intl
3. pdo
4. json
5. redis
6. mongodb
7. yaml
```
If you are sure that all required extensions exist, run this command:
```shell script
/var/www/html# composer install
```
3- Create a schema inside MySQL container

4- Change the DB name inside ```/etc/shop/products.yml``` to match the DB name in step 3

5- Run migrations:
```shell script
/var/www/html# ./app/common/library/vendor/bin/phalcon migration --action=run --config=../../../etc/shop/products.yml --migrations=app/migrations/
```

5- Edit configuration in ```/etc/shop/products.yml``` to match your current settings for MySQL, MongoDB, RabbitMQ and Redis connections.

6- Run supervisord && supervisorctl update to update your supervisor programs and make sure that the status of the programs are READY, or you can check if there are two new queues (products_sync, product_async) by going to http://rabbitmq-container-ip:15672/#/queues

7 - Import API collection into postman to start using this service

8- To run unit test:
```shell script
/var/www/html# ./app/common/library/vendor/bin/phpunit -c tests/phpunit.xml
```