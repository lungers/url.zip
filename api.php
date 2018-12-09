<?php

$db = new SQLite3('shuri.db');
$db->exec('CREATE TABLE IF NOT EXISTS urls (
    ID varchar(5) NOT NULL UNIQUE,
    LongUrl text NOT NULL UNIQUE,
    PRIMARY KEY (ID)
)');

$dir = dirname($_SERVER['PHP_SELF']);

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

        $stmp = $db->prepare('INSERT OR IGNORE INTO urls (ID, LongUrl) VALUES (:id, :url)');
        $stmp->bindValue(':id', $urlhash);
        $stmp->bindValue(':url', $url);
        $stmp->execute();

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

    exit;
} elseif (!empty($_GET)) {
    $urlhash = key($_GET);

    $stmp = $db->prepare('SELECT LongUrl FROM urls WHERE ID = :id');
    $stmp->bindValue(':id', $urlhash);

    $res = $stmp->execute()->fetchArray();
    $url = $res['LongUrl'] ? $res['LongUrl'] : $dir;

    header('Location:' . $url);
    exit;
}

function smallHash($text) {
    $t = rtrim(base64_encode(hash('crc32', $text, true)), '=');
    return strtr($t, '+/', '-_');
}
