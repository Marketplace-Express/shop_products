Shop: Products Service
--
### Installation:

After starting a new container, go inside it and do the following:

1- Install dependencies:
```text
Note: Type "php -m" and make sure these extensions are installed before:
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
/var/www/html:$ composer install
```

2- Edit configuration in ```/etc/shop/products.yml``` to match your current settings for MySQL, MongoDB, RabbitMQ and Redis connections.

3- Run supervisord && supervisorctl update to update your supervisor programs and make sure that the status of the programs are READY, or you can check if there are two new queues (products_sync, product_async) by going to http://rabbitmq-container-ip:15672/#/queues

4- Import API collection into postman to start using this service