NOW-web
=======

Web managment client for NOW. This is just a full site dump. Future plans are to make this a package so that it can be added into Laravel.

## Install
You can clone this whole site and place it under apache. Or, if you already have a site up, just copy the MVC components into your existing install and add in the routes.

## Basic Laravel Install
1. Install the Laravel framework. This is a breif process for a more detailed install refer to http://laravel.com/
```
cd to /var/www/html
wget https://github.com/laravel/laravel/archive/master.zip
unzip master.zip
composer install
chown -R apache:apache laravel
```
Next edit composer.json to add Laravel packages.
```
"rcrowe/twigbridge": "0.5.*",
"dready92/php-on-couch": "dev-master"
```
Update Laravel.
```
composer update
```
Configure the site by reviewing /laravel/app/config/* and editing as appropriate
What to check if things are not working:
1. check selinux types
2. apache allow overrides 
3. .htaccess files are enabled
4. setsebool -P httpd_can_network_connect 1
