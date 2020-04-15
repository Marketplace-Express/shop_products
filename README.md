Shop: Products Service
--
### Installation:

1. Clone the repository:
```shell script
git clone git@gitlab.com:shop_ecommerce/shop_products.git
```

2- Open Dockerfile and check these arguments if they match your OS architecture (x86 or x64): ```ARG PHALCON_EXT_PATH=php7/64bits```

3- Rename file “config.example.php” under “app/config” to “config.php” then change the parameters to match the your preferences, example:
```php
'database' => [
    'adapter' => 'Mysql',
    'host' => 'network-gateway-ip',
    'port' => 3306,
    'username' => 'mysql-username',
    'password' => 'mysql-password',
    'dbname' => 'shop_products',
    'charset' => 'utf8'
],
'mongodb' => [
    'host' => 'network-gateway-ip',
    'port' => 27017,
    'username' => null,
    'password' => null,
    'dbname' => 'shop_products'
],
```
And so on for Redis and RabbitMQ...
       
4- Run 
```bash
docker build --rm -t shop/products:latest . && docker-compose up -d
```
This command will create new containers:
```
shop_products_products-sync_1: This will declare a new queue “products_sync” in RabbitMQ queues list
shop_products_products-async_1: This will declare a new queue “products_async” in RabbitMQ queues list
shop_products_products-api_1: This will start a new application server listening on a specific port
shop_products_products-unit-test_1: This will run the unit test for this micro-service
```
API service will listen to a specified port in docker-compose file, you can access it by going to this URL: [http://localhost:port](http://localhost:1001)
- The default port value is 1001.
- You can use Postman with the collections provided to test micro-service APIs.

If you want to scale up the workers (sync / async), you can simply run this command:
```bash
docker-compose up --scale products-{sync/async}=num -d
```
Where “num” is the number of processes to run, {sync/async} is the service which you want to scale up, example:
```bash
docker-compose up --scale products-async=3 -d
```

---
### Unit test
To run the unit test, just run this command:
```bash
docker-compose up products-unit-test
```