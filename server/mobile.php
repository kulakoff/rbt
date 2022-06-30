<?php

mb_internal_encoding("UTF-8");
require_once "backends/backend.php";
require_once "utils/loader.php";
require_once "utils/guidv4.php";
require_once "utils/db_ext.php";
require_once "utils/checkint.php";

$LanTa_services = [
    'internet' => [ "icon" => "internet", "title" => "Интернет", "description" => "Высокоскоростной доступ в интернет", "canChange" => "t" ],
    'iptv' => [ "icon" => "iptv", "title" => "Телевидение", "description" => "Более 200 каналов", "canChange" => "t" ],
    'ctv' => [ "icon" => "ctv", "title" => "Телевидение", "description" => "Менее 200 каналов", "canChange" => "t" ],
    'phone' => [ "icon" => "phone", "title" => "Телефония", "description" => "Местная и прочая телефония", "canChange" => "t" ],
    'cctv' => [ "icon" => "cctv", "title" => "Видеонаблюдение", "description" => "Всё под контролем", "canChange" => "f" ],
    'domophone' => [ "icon" => "domophone", "title" => "Умный домофон", "description" => "Смотри кто пришёл", "canChange" => "f" ],
    'gsm' => [ "icon" => "gsm", "title" => "Мобильная связь", "description" => "Бла-бла-бла 2", "canChange" => "t" ],
    'faceid' => [ "icon" => "faceid", "title" => "Распознование лица", "description" => "Бла-бла-бла 3", "canChange" => "t" ],
];

$bearer = [];
$cache = [];
$config = false;

$offsetForCityId = 1000000;

try {
    $config = @json_decode(file_get_contents("config/config.json"), true);
} catch (Exception $e) {
    error_log(print_r($e, true));
    response(555, [
        "error" => "config",
    ]);
}

if (!$config) {
    response(555, [
        "error" => "noConfig",
    ]);
}

$backends = [];

$redis_cache_ttl = $config["redis"]["cache_ttl"] ? : 3600;

try {
    $redis = new Redis();
    $redis->connect($config["redis"]["host"], $config["redis"]["port"]);
    if (@$config["redis"]["password"]) {
        $redis->auth($config["redis"]["password"]);
    }
    $redis->setex("iAmOk", 1, "1");
} catch (Exception $e) {
    error_log(print_r($e, true));
    response(555, [
        "error" => "redis",
    ]);
}

try {
    $db = new PDO_EXT(@$config["db"]["dsn"], @$config["db"]["username"], @$config["db"]["password"], @$config["db"]["options"]);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    error_log(print_r($e, true));
    response(555, [
        "error" => "PDO",
    ]);
}

function response($code = 204, $data = false, $name = false, $message = false) {
    global $response_data_source, $response_cahce_req, $response_cache_ttl;
    $response_codes = [
        200 => [ 'name' => 'OK', 'message' => 'Хорошо' ],
        201 => [ 'name' => 'Created', 'message' => 'Создано' ],
        202 => [ 'name' => 'Accepted', 'message' => 'Принято' ],
        203 => [ 'name' => 'Non-Authoritative Information', 'message' => 'Информация не авторитетна' ],
        204 => [ 'name' => 'No Content', 'message' => 'Нет содержимого' ],
        205 => [ 'name' => 'Reset Content', 'message' => 'Сбросить содержимое' ],
        206 => [ 'name' => 'Partial Content', 'message' => 'Частичное содержимое' ],
        207 => [ 'name' => 'Multi-Status', 'message' => 'Многостатусный' ],
        208 => [ 'name' => 'Already Reported', 'message' => 'Уже сообщалось' ],
        226 => [ 'name' => 'IM Used', 'message' => 'Использовано IM' ],
        400 => [ 'name' => 'Bad Request', 'message' => 'Плохой, неверный запрос' ],
        401 => [ 'name' => 'Unauthorized', 'message' => 'Не авторизован' ],
        402 => [ 'name' => 'Payment Required', 'message' => 'Необходима оплата' ],
        403 => [ 'name' => 'Forbidden', 'message' => 'Запрещено' ],
        404 => [ 'name' => 'Not Found', 'message' => 'Не найдено' ],
        405 => [ 'name' => 'Method Not Allowed', 'message' => 'Метод не поддерживается' ],
        406 => [ 'name' => 'Not Acceptable', 'message' => 'Неприемлемо' ],
        407 => [ 'name' => 'Proxy Authentication Required', 'message' => 'Необходима аутентификация прокси' ],
        408 => [ 'name' => 'Request Timeout', 'message' => 'Истекло время ожидания' ],
        409 => [ 'name' => 'Conflict', 'message' => 'Конфликт' ],
        410 => [ 'name' => 'Gone', 'message' => 'Удалён' ],
        411 => [ 'name' => 'Length Required', 'message' => 'Необходима длина' ],
        412 => [ 'name' => 'Precondition Failed', 'message' => 'Условие ложно' ],
        413 => [ 'name' => 'Payload Too Large', 'message' => 'Полезная нагрузка слишком велика' ],
        414 => [ 'name' => 'URI Too Long', 'message' => 'URI слишком длинный' ],
        415 => [ 'name' => 'Unsupported Media Type', 'message' => 'Неподдерживаемый тип данных' ],
        416 => [ 'name' => 'Range Not Satisfiable', 'message' => 'Диапазон не достижим' ],
        417 => [ 'name' => 'Expectation Failed', 'message' => 'Ожидание не удалось' ],
        418 => [ 'name' => 'I’m a teapot', 'message' => 'Я — чайник' ],
        419 => [ 'name' => 'Authentication Timeout (not in RFC 2616)', 'message' => 'Обычно ошибка проверки CSRF' ],
        421 => [ 'name' => 'Misdirected Request', 'message' => 'Запрос направлен неверно' ],
        422 => [ 'name' => 'Unprocessable Entity', 'message' => 'Необрабатываемый экземпляр' ],
        423 => [ 'name' => 'Locked', 'message' => 'Заблокировано' ],
        424 => [ 'name' => 'Failed Dependency', 'message' => 'Невыполненная зависимость' ],
        426 => [ 'name' => 'Upgrade Required', 'message' => 'Необходимо обновление' ],
        428 => [ 'name' => 'Precondition Required', 'message' => 'Необходимо предусловие' ],
        429 => [ 'name' => 'Too Many Requests', 'message' => 'Слишком много запросов' ],
        431 => [ 'name' => 'Request Header Fields Too Large', 'message' => 'Поля заголовка запроса слишком большие' ],
        449 => [ 'name' => 'Retry With', 'message' => 'Повторить с' ],
        451 => [ 'name' => 'Unavailable For Legal Reasons', 'message' => 'Недоступно по юридическим причинам' ],
        499 => [ 'name' => 'Client Closed Request', 'message' => 'Клиент закрыл соединение' ],
        503 => [ 'name' => 'Service Unavailable', 'message' => 'Сервис недоступен' ],
    ];
    header('Content-Type: application/json');
    http_response_code($code);
    if ((int)$code < 300 && $response_cahce_req && $response_data_source == 'db' && (int)$response_cache_ttl > 0) {
//        $redis->setEx($response_cahce_req, $response_cache_ttl, json_encode([
//            'code' => $code,
//            'data' => $data,
//        ], JSON_UNESCAPED_UNICODE));
    }
    if ((int)$code == 204) {
        exit;
    }
    $ret = [
        'code' => $code,
    ];
    if (!$message) {
        if ($name) {
            $message = $name;
        } else {
            $message = @$response_codes[$code]['message'];
        }
    }
    if (!$name) {
        $name = @$response_codes[$code]['name'];
    }
    if ($name) {
        $ret['name'] = $name;
    }
    if ($message) {
        $ret['message'] = $message;
    }
    if ($data) {
        $ret['data'] = $data;
    }
    echo json_encode($ret, JSON_UNESCAPED_UNICODE);
    exit;
}

function mkdir_r($dirName, $rights = 0777) {
    $dirs = explode('/', $dirName);
    $dir = '';
    foreach ($dirs as $part) {
        $dir .= $part.'/';
        if (!is_dir($dir) && strlen($dir) > 0) {
            mkdir($dir, $rights);
            chmod($dir, $rights);
        }
    }
}

function auth($_response_cache_ttl = -1) {
    global $_SERVER, $bearer, $response_cache_ttl;
    if ($_response_cache_ttl >= 0) {
        $response_cache_ttl = $_response_cache_ttl;
    }
    $ip = long2ip(ip2long($_SERVER['REMOTE_ADDR']));
    if ($ip == '127.0.0.1' && !@$_SERVER['HTTP_AUTHORIZATION'] && $_GET['phone']) {
//        $p = pg_escape_string(trim($_GET['phone']));
        // TODO: добавить проверку валидности токена.
        $bearer = true;
//        $bearer = @pg_fetch_assoc(pg_query("select * from domophones.bearers where id = '$p'"));
        if (!$bearer) {
            response(403, false, "Ошибка авторизации", "Ошибка авторизации");
        }
    } else {
        if (!@$_SERVER['HTTP_AUTHORIZATION']) {
            response(403, false, "Ошибка авторизации", "Ошибка авторизации");
        }
        $bearer = @pg_escape_string(trim(explode('Bearer', $_SERVER['HTTP_AUTHORIZATION'])[1]));
        if (!$bearer) {
            response(422, false, "Отсутствует токен авторизации", "Отсутствует токен авторизации");
        }
//        $t_ = $bearer;
        // TODO: добавить проверку валидности токена пользователя.
        $bearer = true;
//        $bearer = pg_fetch_assoc(pg_query("select * from domophones.bearers where token = '$t_'"));
        if (!$bearer) {
            response(401, false, "Не авторизован", "Не авторизован");
        }
        // TODO: добавить обновление последнего использования токена пользователем
//        pg_query("update domophones.bearers set last_seen = now() where token = '$t_'");
//        $action = mysqli_escape_string($mysql, $_SERVER['REQUEST_URI']);
//        mysql("insert into dm.applog (date, id, action, ip) values (now(), '{$bearer['id']}', '$action', inet_aton('$ip'))");
    }
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $raw_postdata = file_get_contents("php://input");
    $postdata = json_decode($raw_postdata, true);
    $m = explode('/', $_SERVER["REQUEST_URI"]);

    if (count($m) == 5 && !$m[0] && $m[2] == 'mobile.php') {
        $module = $m[3];
        $method = $m[4];
        if (file_exists("mobile/{$module}/{$method}.php")) {
              $redis = new Redis();
//            $redis->connect('127.0.0.1');
            $b = @explode(' ', $_SERVER['HTTP_AUTHORIZATION'])[1];
            if ($b) {
                $response_cahce_req = strtolower($module . '-' . $method . '-' . $b . '-' . md5(serialize($postdata)));
//                $cache = @json_decode($redis->get($response_cahce_req), true);
                $cache = false;
            } else {
                $response_cahce_req = false;
                $cache = false;
            }
            if ($cache && !array_key_exists('X-Dm-Api-Refresh', apache_request_headers())) {
//                $redis->incr('cache-hit');
//                $response_data_source = 'cache';
//                header("X-Dm-Api-Data-Source: $response_data_source");
//                response($cache['code'], $cache['data']);
            } else {
                if (array_key_exists('X-Dm-Api-Refresh', apache_request_headers())) {
                    // $redis->incr('cache-force-miss');
                } else {
                    // $redis->incr('cache-miss');
                }
                $response_data_source = 'db';
                $response_cache_ttl = 60;
                header("X-Dm-Api-Data-Source: $response_data_source");
                require_once "mobile/{$module}/{$method}.php";
            }
        }
    }
    response(405);
}
response(404);
