<?php
use Dotenv\Dotenv;

require 'vendor/autoload.php';

$dotenv = new Dotenv(__DIR__);
$dotenv->load();

return [
    "paths"        => [
        "migrations" => "%%PHINX_CONFIG_DIR%%/resources/migrations",
    ],
    "environments" => [
        "default_migration_table" => "phinxlog",
        "default_database"        => "default",
        "default"              => [
            "adapter" => "mysql",
            "host"    => 'localhost',
            "name"    => getenv('DB_DATABASE'),
            "user"    => getenv('DB_USERNAME'),
            "pass"    => getenv('DB_PASSWORD'),
            "port"    => 3306,
            "charset" => "utf8",
        ],
    ],
];
