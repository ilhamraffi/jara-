<?php

try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
    echo 'MySQL connected'.PHP_EOL;

    $pdo->exec('CREATE DATABASE IF NOT EXISTS jara');
    echo 'Database jara created/exists'.PHP_EOL;

    $pdo->exec('USE jara');
    echo 'Using database jara'.PHP_EOL;
} catch (Exception $e) {
    echo 'Error: '.$e->getMessage().PHP_EOL;
}
