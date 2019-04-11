<?php

define('MYSQL_SERVER', '');
define('MYSQL_USER', '');
define('MYSQL_PASSWORD', '');
define('MYSQL_DATABASE', '');

$dir = rtrim(dirname($_SERVER['PHP_SELF']), '/');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $post = file_get_contents('php://input');
    $post = json_decode($post, true);

    $url = isset($post['url']) ? $post['url'] : '';

    if (preg_match('#^(https?://)?\S*?\.\S*?$#', $url)) {
        if (strpos($url, 'http://') !== 0 && strpos($url, 'https://') !== 0) {
            $url = 'http://' . $url;
        }

        $urlhash = smallHash($url);
        $shortUrl = $_SERVER['HTTP_HOST'] . $dir . '/' . $urlhash;

        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
            $shortUrl = 'https://' . $shortUrl;
        } else {
            $shortUrl = 'http://' . $shortUrl;
        }

        addUrl($url, $urlhash);

        echo json_encode(array(
            'ok' => true,
            'id' => $urlhash,
            'short_url' => $shortUrl
        ));
    } else {
        echo json_encode(array(
            'ok' => false,
            'message' => 'Invalid url.'
        ));
    }
} elseif (!empty($_GET)) {
    $urlhash = key($_GET);
    $res = getUrl($urlhash);
    $url = ($res !== null) ? $res['LongUrl'] : ((empty($dir)) ? '/' : $dir);

    header('Location:' . $url);
}

function addUrl($url, $hash) {
    $conn = new mysqli(MYSQL_SERVER, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE);
    $url = $conn->real_escape_string($url);
    $hash = $conn->real_escape_string($hash);
    $sql = "INSERT IGNORE INTO urls (ID, LongUrl) VALUES ('$hash', '$url')";
    $conn->query($sql);
    $conn->close();
}

function getUrl($hash) {
    $conn = new mysqli(MYSQL_SERVER, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE);
    $hash = $conn->real_escape_string($hash);
    $sql = "SELECT LongUrl FROM urls WHERE ID = '$hash'";
    $res = $conn->query($sql);
    $conn->close();

    return !empty($res) ? $res->fetch_assoc() : null;
}

function smallHash($text) {
    $t = rtrim(base64_encode(hash('crc32', $text, true)), '=');
    return strtr($t, '+/', '-_');
}
