<?php

namespace UrlShortener;

use Exception;
use PDO;

class LinkShortener
{
    private static $symbols = '123456789bcdfghjkmnpqrstvwxyzBCDFGHJKLMNPQRSTVWXYZ';
    private static $table = 'short_urls';

    private $pdo;
    private $timestamp;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->timestamp = $_SERVER['REQUEST_TIME'];
    }

    public function urlToShortCode($url)
    {
        if (empty($url)) {
            throw new \InvalidArgumentException("Url must not be empty");
        }

        if (!$this->validateUrlFormat($url)) {
            throw new \InvalidArgumentException("URL is invalid");
        }

        if (!$this->urlExist($url)) {
            throw new \InvalidArgumentException("This url doesn't exist");
        }

        $shortUrl = $this->getShortUrl($url);
        if (!$shortUrl) {
            $shortUrl = $this->createShortUrl($url);
        }

        return $shortUrl;
    }

    private function getShortUrl($url)
    {
        $query = "SELECT short_code FROM " . self::$table .
            " WHERE long_url = :long_url LIMIT 1";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            'long_url' => $url
        ]);

        $result = $stmt->fetch();
        return empty($result) ? false : $result['short_code'];
    }

    private function validateUrlFormat($url)
    {
        return filter_var($url, FILTER_VALIDATE_URL,
            FILTER_FLAG_HOST_REQUIRED);
    }

    private function urlExist($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch,  CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        $response = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return (!empty($response) && $response != 404);
    }

    private function createShortUrl($url)
    {
        $id = $this->insertUrlInDb($url);
        $shortCode = $this->convertIdToShortLink($id);
        $this->insertShortCodeInDb($id, $shortCode);
        return $shortCode;
    }

    public function createShortWithCustom($url, $custom)
    {
        $id = $this->insertUrlInDb($url);
        $shortCode = $custom;
        $this->insertShortCodeInDb($id, $shortCode);
        return $shortCode;
    }

    private function insertUrlInDb($url)
    {
        $query = "INSERT INTO " . self::$table . " (long_url) VALUES(:url)";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            'url' => $url
        ]);

        return $this->pdo->lastInsertId();
    }

    private function convertIdToShortLink($id)
    {
        $id = intval($id);
        $length = strlen(self::$symbols);

        $code = "";
        while ($id > $length - 1) {
            $code = self::$symbols[intval(fmod($id, $length))] . $code;
            $id = floor($id / $length);
        }

        $id = intval($id);

        $code .= self::$symbols[$id];

        return $code;
    }

    private function insertShortCodeInDb($id, $shortCode)
    {
        $query = "UPDATE " . self::$table .
            " SET short_code = :short_code WHERE id = :id";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            'short_code' => $shortCode,
            'id' => $id
        ]);
    }

    public function shortCodeToUrl($code, $increment = true) {
        if (empty($code)) {
            throw new Exception("No short code was supplied.");
        }

        if ($this->validateShortCode($code) == false) {
            throw new Exception(
                "Short code does not have a valid format.");
        }

        $urlRow = $this->getUrlFromDb($code);
        if (empty($urlRow)) {
            throw new Exception(
                "Short code does not appear to exist.");
        }

        if ($increment == true) {
            $this->incrementCounter($urlRow["id"]);
        }

        return $urlRow["long_url"];
    }

    protected function validateShortCode($code) {
        return preg_match("|[" . self::$symbols . "]+|", $code);
    }

    protected function getUrlFromDb($code) {
        $query = "SELECT id, long_url FROM " . self::$table .
            " WHERE short_code = :short_code LIMIT 1";
        $stmt = $this->pdo->prepare($query);
        $params = [
            "short_code" => $code
        ];
        $stmt->execute($params);

        $result = $stmt->fetch();
        return (empty($result)) ? false : $result;
    }

    protected function incrementCounter($id) {
        $query = "UPDATE " . self::$table .
            " SET counter = counter + 1 WHERE id = :id";
        $stmt = $this->pdo->prepare($query);
        $params = [
            "id" => $id
        ];
        $stmt->execute($params);
    }
}