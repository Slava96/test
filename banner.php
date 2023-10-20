<?php
require_once '/usr/local/config/db_connect.php';
require_once 'ip_utils.php';

$url = $_SERVER['HTTP_REFERER'];
if (empty($url)) {
    $uri = $_SERVER['REQUEST_URI'];
    $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
}

$ip = getIpAddress();
$user_agent = $_SERVER['HTTP_USER_AGENT'];
try {
    $conn = openConnection();

    $stmt = $conn->prepare("INSERT INTO test.unique_views(ip_address, user_agent, view_date, page_url, views_count) VALUES (?, ?, NOW(), ?, 1) ON DUPLICATE KEY UPDATE views_count=views_count+1, view_date=NOW()");
    $stmt->bind_param("sss", $ip, $user_agent, $url);
    $stmt->execute();
} catch (DbCreateException $ex) {
    error_log('Cannot construct MySQL connection: ' . $ex->getMessage() . '\n', 3, "/var/tmp/errors.log");
} catch (Exception $ex) {
    error_log('Catch exception while work with MySQL: ' . $ex->getMessage() . '\n', 3, "/var/tmp/errors.log");
}

header('Content-Type: image/png');
readfile("./images/cross.png");