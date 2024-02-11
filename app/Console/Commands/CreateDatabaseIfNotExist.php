<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class CreateDatabaseIfNotExist extends Command
{
    protected $signature = 'database:create-if-not-exist';

    protected $description = 'Create the database if it does not exist';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $databaseName = Config::get('database.connections.mysql.database');
        $connection = Config::get('database.connections.mysql');

        try {
            $pdo = new \PDO("mysql:host={$connection['host']};charset=utf8mb4", $connection['username'], $connection['password']);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$databaseName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
            $this->info("Database '{$databaseName}' created or already exists.");
        } catch (\PDOException $e) {
            $this->error("Could not connect to the database. Please check your configuration. Error: " . $e->getMessage());
            return;
        }

        $this->call('migrate');
    }
}
