#!/bin/bash

MYSQL_DATA=/home/runner/mysql-data
MYSQL_RUN=/home/runner/mysql-run
MYSQL_SOCK=$MYSQL_RUN/mysql.sock
MYSQL_LOG=$MYSQL_RUN/mysql.err
APP_DIR=/home/runner/workspace

mkdir -p $MYSQL_DATA $MYSQL_RUN

# Stop any existing mysqld
pkill -f "mysqld.*$MYSQL_DATA" 2>/dev/null || true
sleep 1
rm -f $MYSQL_SOCK $MYSQL_RUN/mysql.pid

# Start MariaDB
mysqld --no-defaults \
  --datadir=$MYSQL_DATA \
  --socket=$MYSQL_SOCK \
  --pid-file=$MYSQL_RUN/mysql.pid \
  --port=3306 \
  --bind-address=127.0.0.1 \
  --log-error=$MYSQL_LOG \
  --skip-grant-tables \
  --skip-networking=0 \
  --character-set-server=utf8mb4 \
  --collation-server=utf8mb4_unicode_ci \
  2>>$MYSQL_LOG &

MYSQLD_PID=$!
echo "MariaDB starting (PID $MYSQLD_PID)..."

# Wait for socket
for i in $(seq 1 30); do
  if [ -S $MYSQL_SOCK ] && mysqladmin --socket=$MYSQL_SOCK ping 2>/dev/null; then
    echo "MariaDB ready!"
    break
  fi
  sleep 1
done

if ! [ -S $MYSQL_SOCK ]; then
  echo "ERROR: MariaDB failed to start"
  cat $MYSQL_LOG
  exit 1
fi

# Setup database if not already done
if ! mysql --socket=$MYSQL_SOCK -u root -e "USE bimaguru; SELECT COUNT(*) FROM users;" 2>/dev/null; then
  echo "Setting up database schema..."
  
  # Create DB and run schema
  mysql --socket=$MYSQL_SOCK -u root <<'SQL'
CREATE DATABASE IF NOT EXISTS bimaguru CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
SQL

  # Run schema (skip CREATE DATABASE and USE lines)
  mysql --socket=$MYSQL_SOCK -u root bimaguru < $APP_DIR/database/schema_only.sql
  
  echo "Loading seed data..."
  mysql --socket=$MYSQL_SOCK -u root bimaguru < $APP_DIR/database/seed.sql
  
  touch $APP_DIR/.installed
  echo "Database setup complete!"
else
  echo "Database already set up."
fi

# Ensure uploads directory exists
mkdir -p $APP_DIR/uploads

echo ""
echo "Starting PHP server on 0.0.0.0:5000..."
echo ""
cd $APP_DIR
exec php -S 0.0.0.0:5000
