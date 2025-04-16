<?php
$envContent = <<<ENV
APP_NAME=ElearningApp
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost/xampp/elearning-html-template/public

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=elearning_db
DB_USERNAME=root
DB_PASSWORD=
ENV;

file_put_contents('.env', $envContent);
echo ".env file generated successfully!\n";
