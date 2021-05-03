-- Create MySQL database user
CREATE USER phalcon IDENTIFIED WITH mysql_native_password BY 'secret';

-- Create schema
CREATE SCHEMA shop_products;

-- Grant all privileges in database to user
GRANT ALL PRIVILEGES ON shop_products.* TO phalcon;