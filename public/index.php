<?php

use App\RequestHandler as Handler;

include __DIR__ . '/../vendor/autoload.php';

$data = file_get_contents('php://input');
$data_decoded = json_decode($data, true)['message'];
['id' => $chat_id, 'username' => $user_login] = $data_decoded['chat'];
$text = $data_decoded['text'] ?? '';
$query = [
    'chat_id' => $chat_id,
    'text' => 'Не понимаю (',
    'parse_mode' => 'html'
];
if ($text === '/start') {
    $query['text'] = Handler::addUser($user_login);
} else if (Handler::isNeedBalance($text)) {
    Handler::getBalance($user_login, $query['text']);
} else if (Handler::isCorrectNumber($text)) {
    Handler::balanceProcessing($user_login, $text, $query['text']);
}
Handler::reply($query);
