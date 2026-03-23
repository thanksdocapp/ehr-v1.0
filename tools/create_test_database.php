<?php

$host = getenv('DB_TEST_HOST') ?: getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_TEST_PORT') ?: getenv('DB_PORT') ?: '3306';
$user = getenv('DB_TEST_USERNAME') ?: getenv('DB_USERNAME') ?: 'root';
$pass = getenv('DB_TEST_PASSWORD') !== false ? getenv('DB_TEST_PASSWORD') : (getenv('DB_PASSWORD') ?: '');
$name = getenv('DB_TEST_DATABASE') ?: 'ehr_testing';

$dsn = "mysql:host={$host};port={$port}";
$pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$pdo->exec(
    "CREATE DATABASE IF NOT EXISTS `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
);
echo "Database `{$name}` is ready.\n";
