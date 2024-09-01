<?php

namespace App;

include __DIR__ . '/../config.php';

class RequestHandler {

    private const MAX_SUM = '100000000000000.00';

    private const PG_CONNECTION_DATA = "host=tg_bot_db user=tg_bot_user"
    . " password=tg_bot_password dbname=tg_bot_pgsql_db";

    public static function addUser($user_login) {
        $pg_connection = pg_connect(self::PG_CONNECTION_DATA);
        $res = pg_insert($pg_connection, 'users', ['user_login' => $user_login]);
        pg_close($pg_connection);
        return $res ? 'Пользователь добавлен!' : 'Ошибка! Пользователь существует!';
    }

    public static function balanceProcessing($user_login, $text, &$message) {
        $pg_connection = pg_connect(self::PG_CONNECTION_DATA);        
        [$res] = (array)pg_select($pg_connection, 'users', ['user_login' => $user_login]);
        $add_balance = str_replace(',', '.', $text);
        $balance = str_replace(['$', ','], ['', ''], $res['balance']);
        $new_balance = bcadd($balance, $add_balance, 2);
        if (bccomp('0', $new_balance, 2) > 0) {
            $message = 'Ошибка! Максимальная сумма для снятия $' . $balance;
        } else if (bccomp($new_balance, self::MAX_SUM, 2) > 0) {
            $message = 'Ошибка! На счете можно хранить не более $' . self::MAX_SUM;
        } else {
            pg_send_query_params(
                $pg_connection,
                'UPDATE users SET balance = $1 WHERE user_login = $2',
                [$new_balance, $user_login]
            );
            $message = 'Успешо! На счете $' . $new_balance;
        }
        pg_close($pg_connection);
    }

    public static function getBalance($user_login, &$text) {
        $pg_connection = pg_connect(self::PG_CONNECTION_DATA);
        [$res] = (array)pg_select($pg_connection, 'users', ['user_login' => $user_login]);
        if ($res) {
            $text = str_replace(',', '', $res['balance']);
        }
        pg_close($pg_connection);
    }

    public static function isCorrectNumber($text) {
        return preg_match('/^-?(0|[1-9]\d*)([,.]\d{1,2})?$/', $text);
    }

    public static function isNeedBalance($text) {
        return str_contains(mb_strtolower($text), 'баланс');
    }

    public static function reply($query) {
        $ch = curl_init('https://api.telegram.org/bot' . BOT_TOKEN
            . '/sendMessage?' . http_build_query($query));
        $curl_options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HEADER => false
        ];
        curl_setopt_array($ch, $curl_options);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}
