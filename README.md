## Requirements

PHP 7.3+
Composer
Access to CLI

## Installation

Unzip project into youre prefered location.

Using CLI cd into project directory( where project was unzipped)

Install dependecies using ### `composer install`

Create a new file .env

Copy contents in .env.example into .env
Update your database details in .env
Update your stripe keys in .env

run `php migrations.php` to migrate tables
run `php seeder.php` to generate documentation
run `php -S localhost:8080` to startup the application

##Documentation  
https://documenter.getpostman.com/view/3587677/2s93eVWthH
