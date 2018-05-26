<?php

require_once __DIR__ . '/../vendor/autoload.php';

use UrlShortener\Render;

$url_parts = explode('?', $_SERVER['REQUEST_URI'], 2);
$url = $url_parts[0];

if ($url === '/getLinkForm') {
    Render::renderFile(__DIR__ . '/../src/getLinkForm.php');
} else if ($url === '/getLink') {
    Render::renderFile(__DIR__ . '/../src/getLink.php');
} else if ($url === '/') {
    Render::renderFile(__DIR__ . '/../src/redirect.php');
}
