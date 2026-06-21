CREATE DATABASE IF NOT EXISTS telega_laravel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS telega_fastapi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE USER IF NOT EXISTS 'telega_laravel_user'@'%' IDENTIFIED BY 'telega';
CREATE USER IF NOT EXISTS 'telega_fastapi_user'@'%' IDENTIFIED BY 'telega';

GRANT ALL PRIVILEGES ON telega_laravel.* TO 'telega_laravel_user'@'%';
GRANT ALL PRIVILEGES ON telega_fastapi.* TO 'telega_fastapi_user'@'%';

FLUSH PRIVILEGES;