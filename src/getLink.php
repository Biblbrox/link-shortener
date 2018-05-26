<?php
use UrlShortener\LinkShortener;
$url = isset($_POST['original-url']) ? $_POST['original-url'] : null;
$customUrl = isset($_POST['custom-url']) ? $_POST['custom-url'] : null;
if (isset($url)) {
    $config = require __DIR__ . '/../config/database.php';
    try {
        $pdo = new PDO($config['dsn'], $config['username'], $config['password']);
    } catch (PDOException $e) {
        printf("Unable to connect to database: %s", $e->getMessage());
        exit;
    }

    $siteUrl = 'http://urlshortener.loc';
    $shortener = new LinkShortener($pdo);
    try {
        if ($customUrl) {
            $query = 'SELECT long_url FROM short_urls WHERE long_url = :long_url';
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                'long_url' => $url
            ]);
            $result = $stmt->fetch();
            if (!empty($result)) {
                echo json_encode([
                    'error' => 'URl для данного алиаса уже существует'
                ]);
                exit;
            } else {
                echo json_encode([
                    'short_link' => $siteUrl . "?c=" . $shortener->createShortWithCustom($url, $customUrl)
                ]);
                exit;
            }
        } else {
            $shortLink = $siteUrl . "?c=" . $shortener->urlToShortCode($url);
            echo json_encode([
                'short_link' => $shortLink
            ]);
            exit;
        }
    } catch (Exception $e) {
        echo json_encode([
            'error' => $e->getMessage()
        ]);
        exit;
    }
}

exit;