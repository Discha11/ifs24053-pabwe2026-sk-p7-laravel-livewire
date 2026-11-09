# Studi Kasus: Aplikasi Stock Barang (Laravel 13 + Livewire 4)

Paket ini adalah **overlay**: berisi file-file yang harus ditimpa/ditambahkan ke proyek Laravel baru.

## Langkah (PowerShell / terminal)

```bash
# 1. Buat proyek (ganti username)
laravel new ifs24053D:\SEM5\PABWE\Praktikum\laravel-stock>php artisan migrate:fresh --seed


   Illuminate\Database\QueryException 

  SQLSTATE[08006] [7] connection to server at "127.0.0.1", port 5432 failed: FATAL:  password authentication failed for user "postgres" (Connection: pgsql, Host: 127.0.0.1, Port: 5432, Database: pabwe_p7_stock, SQL: select exists (select 1 from pg_class c, pg_namespace n where n.nspname = current_schema() and c.relname = 'migrations' and c.relkind in ('r', 'p') and n.oid = c.relnamespace))

  at vendor\laravel\framework\src\Illuminate\Database\Connection.php:864
    860▕             $exceptionType = ($isUniqueConstraintError = $this->isUniqueConstraintError($e))
    861▕                 ? UniqueConstraintViolationException::class
    862▕                 : QueryException::class;
    863▕ 
  ➜ 864▕             $exception = new $exceptionType(
    865▕                 $this->getNameWithReadWriteType(),
    866▕                 $query,
    867▕                 $this->prepareBindings($bindings),
    868▕                 $e,

  1   vendor\laravel\framework\src\Illuminate\Database\Connectors\Connector.php:67
      PDOException::("SQLSTATE[08006] [7] connection to server at "127.0.0.1", port 5432 failed: FATAL:  password authentication failed for user "postgres"")

  2   vendor\laravel\framework\src\Illuminate\Database\Connectors\Connector.php:67
      PDO::connect()
-pabwe2026-sk-p7-laravel-livewire --database=mysql --phpunit --no-node --no-boost --no-interaction
cd abdullah_ubaid-pabwe2026-sk-p7-laravel-livewire

# 2. Dependensi
composer require livewire/livewire phpoffice/phpspreadsheet

# 3. Ekstrak isi zip overlay ini ke root proyek (timpa file yang sama), lalu:
php patch.php

# 4. Database
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS pabwe_p7_stock CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

Edit `.env`: `DB_DATABASE=pabwe_p7_stock`, `DB_USERNAME`, `DB_PASSWORD`, `APP_URL`, `FORCE_HTTPS=false`, `OPENAI_API_KEY=sk-...`.

```bash
# 5. Migrasi + seeder + storage
php artisan migrate:fresh --seed
php artisan storage:link
php artisan serve
```

Login: `admin / admin` dan `member / member`.

## Pengujian

```bash
composer test                                   # tanpa coverage
vendor/bin/phpunit --coverage-text              # butuh pcov atau xdebug
composer audit
```

> Tes memakai SQLite in-memory bawaan `phpunit.xml` skeleton, jadi aktifkan `pdo_sqlite`, `gd`, `zip`, `mbstring` di php.ini.
