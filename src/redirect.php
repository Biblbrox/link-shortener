<?php

use UrlShortener\LinkShortener;

$config = require __DIR__ . '/../config/database.php';

try {
    $pdo = new PDO($config['dsn'], $config['username'], $config['password']);
} catch (PDOException $e) {
    printf( "Unable to connect to database: %s", $e->getMessage());
    exit;
}
$key = isset($_GET['c']) ? $_GET['c'] : null;
if ($key === null) {
    echo "You must set get parameter c (short link)";
    exit;
}
try {
    $link = (new LinkShortener($pdo))->shortCodeToUrl($key);
    header('Location: ' . $link);
} catch (Exception $e) {
    printf("Something went wrong: %s", $e->getMessage());
    exit;
}