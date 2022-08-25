<?php

/**
 * @api {post} /address/openDoor открыть дверь (калитку, ворота, шлагбаум)
 * @apiVersion 1.0.0
 * @apiDescription ***нуждается в доработке***
 *
 * @apiGroup Address
 *
 * @apiHeader {String} authorization токен авторизации
 *
 * @apiParam {Number} domophoneId идентификатор домофона
 * @apiParam {Number=0,1,2} [doorId=0] идентификатор двери (калитки, ворот, шлагбаума)
 *
 * @apiErrorExample Ошибки
 * 403 требуется авторизация
 * 422 неверный формат данных
 * 404 пользователь не найден
 * 410 авторизация отозвана
 * 424 неверный токен
 */
    //Проверка POST BODY
    if (!$postdata['domophoneId']) response(422);

    auth(15);
    
    $domophone_id = (int)@$postdata['domophoneId'];
    $door_id = (int)@$postdata['doorId'];
    $households = loadBackend("households");
    
    //TODO: добавить проверку на блокировку домофона
    // Проверка блокировки квартиры, получаем список доступных domophoneId
    $userFlats = [];
    foreach($subscriber['flats'] as $flat) { 
    $flatDetail = $households->getFlat($flat['flatId']);

    $flatItem = [];
    $flatItem['flatId'] = $flatDetail['flatId'];
    $flatItem['isBlocked'] = $flatDetail['autoBlock'] || $flatDetail['manualBlock'] ?: false;
    $flatItem['domophones'] = [];

    foreach($flatDetail['entrances'] as $entrace) {
        $flatItem['domophones'][] = $entrace['domophoneId']; 
    }
    $flatItem['isBlocked'] ?: $userFlats[] = $flatItem; 
    }

    $userDomophones=[];
    foreach($userFlats as $dmophoneItem) {
        $userDomophones= [...$userDomophones,...$dmophoneItem['domophones']];
    }
    // Доступне пользователю для управления domophoneIds
    $userDomophones = array_values(array_unique($userDomophones));
    //Возвращаем ответ пользователю если не найден запрошеный domophone_id
    if (!in_array($domophone_id, $userDomophones)) {
        response(404, false, false, 'Услуга недоступна (договор заблокирован либо не оплачен)');
    } 
    // TODO: удалить зуглушку тестового ответа
    response(200,"Тест, дверь открыта");
    
    $domophone = $households->getDomophone($domophone_id);

    $model = loadDomophone($domophone["model"], $domophone["url"], $domophone["credentials"]);
    $model->open_door($door_id);

/*
    if (!$domophone_id) {
        response(422);
    }

    $door_id = (int)@$postdata['doorId'];

    $f = implode(',', array_merge(all_flats(true), all_flats()));
    if (!$f) {
        response(404);
    }

    $f = (int)pg_fetch_result(pg_query("select count(*) from (select domophone_id from domophones.domophones where entrance_id in (select entrance_id from address.flats where not dmblock and flat_id in ($f)) union (select domophone_id from domophones.gates where house_id in (select house_id from address.flats where not dmblock and flat_id in ($f)))) as t where domophone_id=$domophone_id"), 0);
    if (!$f) {
        response(404, false, 'Не найдено', 'Услуга недоступна (договор заблокирован либо не оплачен)');
    }

    try {
        $domophone = open_domophone($domophone_id);
        $domophone->open($door_id);
        $sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        $msg = json_encode([ 'host' => $domophone->ip,  'msg' => "Some door opening by APP command [{$bearer['id']}]" ]);
        $len = strlen($msg);
        socket_sendto($sock, $msg, $len, 0, '127.0.0.1', 45456);
        socket_close($sock);
        mysql("insert into dm.door_open (ip, event, door, detail) values ('{$domophone->ip}', '4', '$door_id', '{$bearer['id']}')");
    } catch (Exception $ex) {
        response(503, false, $ex->getCode(), $ex->getMessage());
    }
*/
    response();
