#!/bin/sh
set -eu

mysql --protocol=socket --user=root --password="${MYSQL_ROOT_PASSWORD}" <<'SQL'
CREATE DATABASE IF NOT EXISTS starter_tasks CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS talred_platform CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
SQL

mysql --protocol=socket --user=root --password="${MYSQL_ROOT_PASSWORD}" <<SQL
GRANT ALL PRIVILEGES ON starter_tasks.* TO '${MYSQL_USER}'@'%';
GRANT ALL PRIVILEGES ON talred_platform.* TO '${MYSQL_USER}'@'%';
FLUSH PRIVILEGES;
SQL
