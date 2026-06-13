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
mysql --socket=$MYSQL_SOCK -u root -e "CREATE DATABASE IF NOT EXISTS bimaguru CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;" 2>/dev/null

USERS_COUNT=$(mysql --socket=$MYSQL_SOCK -u root bimaguru -se "SELECT COUNT(*) FROM users;" 2>/dev/null || echo "0")

if [ "$USERS_COUNT" = "0" ]; then
  echo "Setting up database schema..."
  mysql --socket=$MYSQL_SOCK -u root bimaguru < $APP_DIR/database/schema_only.sql
  echo "Loading seed data..."
  mysql --socket=$MYSQL_SOCK -u root bimaguru < $APP_DIR/database/seed.sql
  touch $APP_DIR/.installed
  echo "Database setup complete!"
else
  # Run schema with IF NOT EXISTS (safe to re-run, adds new tables if any)
  mysql --socket=$MYSQL_SOCK -u root bimaguru < $APP_DIR/database/schema_only.sql 2>/dev/null || true

  # Ensure guru_wali account always exists (repair if accidentally deleted)
  GURU_COUNT=$(mysql --socket=$MYSQL_SOCK -u root bimaguru -se "SELECT COUNT(*) FROM users WHERE role='guru_wali';" 2>/dev/null || echo "0")
  if [ "$GURU_COUNT" = "0" ]; then
    echo "Repairing missing guru_wali account..."
    GURU_HASH=$(php -r "echo password_hash('password', PASSWORD_BCRYPT);")
    mysql --socket=$MYSQL_SOCK -u root bimaguru <<SQL
INSERT INTO users (email, password_hash, nama_lengkap, role, no_telepon, is_active)
VALUES ('guru@bimaguru.local', '$GURU_HASH', 'Ibu Siti Rahayu, S.Pd', 'guru_wali', '081234567890', 1);
SET @uid = LAST_INSERT_ID();
INSERT INTO guru_wali (user_id, nip, bidang_keahlian, bio, max_siswa)
VALUES (@uid, '198501152010012001', 'Bimbingan Konseling', 'Guru wali berpengalaman mendampingi siswa.', 25);
SET @gwid = LAST_INSERT_ID();
INSERT IGNORE INTO penugasan (guru_wali_id, pelajar_id, tanggal_mulai, is_active)
SELECT @gwid, id, CURDATE(), 1 FROM pelajar;
SQL
    echo "Guru account restored."
  fi

  echo "Database already set up."
fi

# Ensure uploads directory exists
mkdir -p $APP_DIR/uploads

echo ""
echo "Starting PHP server on 0.0.0.0:5000..."
echo ""
cd $APP_DIR
exec php -S 0.0.0.0:5000
