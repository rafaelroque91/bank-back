# BNB Bank System - Back-End

Bank System Banc-end created using PHP with Laravel Framework

### ⚙️ Project setup

Edit the .env file to Setup 
- 	DB_DATABASE
- 	DB_USERNAME
- 	DB_PASSWORD

```
composer install
```

```
php artisan migrate --seed
```

```
php artisan passport:client --password
```
*Copy Client ID end Client secret and set in FrontEnd env file.*

### Run in develop mode

```
php artisan serve
```

### Run unit tests

```
vendor/bin/phpunit
```

### generate HTML Coverage Report

```
vendor/bin/phpunit --coverage-html report/
```

*Last Report in: /report folder
TransactionService -> check /report/coverage.png



