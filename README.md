Shop: Products Service
--
### Description:
This service handles products functionality, including CRUDs, processing and handling all logic related to products.

---

### Installation:

1. Clone the repository:
```shell script
git clone git@gitlab.com:shop_ecommerce/shop_products.git
```

2- Rename file “config.example.php” under “app/config” to “config.php” then change the parameters to match your preferences, example:
```php
return new \Phalcon\Config([
    'database' => [
        'adapter' => 'Mysql',
        'host' => 'marketplace-mysql container ip',
        'port' => 3306,
        'username' => 'mysql-username',
        'password' => 'mysql-password',
        'dbname' => 'shop_products',
        'charset' => 'utf8'
    ],
    'mongodb' => [
        'host' => 'marketplace-mongo container ip',
        'port' => 27017,
        'username' => null,
        'password' => null,
        'dbname' => 'shop_products'
    ],
    ...
]);
```
And so on for Redis and RabbitMQ ...
>Note: You can use network (marketplace-network) gateway ip instead of providing each container ip

3. Login to docker registry provider, in order to pull this micro service docker image:
```bash
docker login registry.gitlab.com
```
Provide your user name and password on gitlab, you should have access to the project, so you can pull the image.

4. Pull the docker image from container registry:
```bash
docker pull registry.gitlab.com/shop_ecommerce/shop_products
```
       
5- Run `docker-compose up -d`, This command will create new containers:

    1. shop_products_products-sync_1
    - This will declare a new queue “products_sync” in RabbitMQ queues list
    2. shop_products_products-async_1
    - This will declare a new queue “products_async” in RabbitMQ queues list
    3. shop_products_products-api_1
    - This will start a new application server listening on a specific port specified in `docker-compose.yml` file, you can access it by going to this URL: [http://localhost:port](http://localhost:1001)
    - As a default, the port value is 1001.
    - You can use Postman with the collections provided to test micro service APIs.
    4. shop_products_products-unit-test_1
    - This will run the unit test for this micro service.