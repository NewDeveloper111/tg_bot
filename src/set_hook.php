<?php

include __DIR__ . '/../config.php';

$query = ['url' => BOT_HOST];
$ch = curl_init('https://api.telegram.org/bot' . BOT_TOKEN . '/setWebhook?' 
. http_build_query($query));
$curl_options = [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_HEADER => false
];
curl_setopt_array($ch, $curl_options);
$result = curl_exec($ch);
curl_close($ch);
echo json_decode($result, true)['description']  . PHP_EOL;