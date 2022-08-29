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

    /** 
     * Проверка блоировки квартиры пользователя.
     * Если вход ведет в две и более квартиры одного подъезда, одна из которых заблокирована, вход должен открываться
     * @param boolean $sum
     * @param mixed $flat
     * @return boolean
     */
    function checkFlat($sum, $flat)
    { 
        [
            "autoBlock" => $autoBlock,
            "manualBlock" => $manualBlock,
            "entrances" => $entrances
        ] = $GLOBALS["households"]->getFlat($flat['flatId']);

        $availableDomophones = [];
        foreach ($entrances as $entrace) {
            $availableDomophones[] = $entrace['domophoneId'];
        }
        $availableDomophones = array_values($availableDomophones);

        return $sum +  (!($autoBlock || $manualBlock) && in_array($GLOBALS["domophone_id"], $availableDomophones));
    };

    $isOpenAllowed = @(bool)array_reduce($subscriber['flats'], 'checkFlat');
    $isOpenAllowed ?: response(404, 'Услуга недоступна (договор заблокирован либо не оплачен)');

    // TODO: удалить заглушку тестового ответа
    response(200, "Тест, дверь открыта");

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
