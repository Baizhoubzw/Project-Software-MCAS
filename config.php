<?php
// config.php
// 安全存放：建议放在不被 web root 直接访问的位置，或用环境变量

// DB
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'your_database';
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) die("DB Connect Error: " . $conn->connect_error);

// Xendit Secret Key (Sandbox)
define('XND_SECRET_KEY', 'xnd_development_xxx_replace_with_yours');
// 例如： xnd_development_abcdefg...

// Site base url (no trailing slash), e.g. https://yourdomain.com or http://localhost/project
define('BASE_URL', 'http://localhost/project'); 

// Webhook secret - optional for validation (set same in Xendit dashboard if using)
define('WEBHOOK_SECRET', 'some_random_str_if_you_want');

// helper for curl with basic auth
function xendit_post($url, $data) {
    $ch = curl_init($url);
    $payload = json_encode($data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, XND_SECRET_KEY . ":");
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    $resp = curl_exec($ch);
    if (curl_errno($ch)) {
        throw new Exception('Curl error: ' . curl_error($ch));
    }
    curl_close($ch);
    return json_decode($resp, true);
}
