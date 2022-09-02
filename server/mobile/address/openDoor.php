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

// Проверка POST BODY
if (!$postdata['domophoneId'] || !in_array(intval($postdata['doorId']), range(0,2))) {
    response(422);
};

auth(15);

$domophone_id = (int)@$postdata['domophoneId'];
$door_id = (int)@$postdata['doorId'];
$households = loadBackend("households");

// проверка на блокировку домофона
$blocked = true;
foreach ($subscriber['flats'] as $flat) {
    $flatDetail = $households->getFlat($flat['flatId']);
    if ($flatDetail['autoBlock'] || $flatDetail['manualBlock']) {
        continue;
    }

    foreach ($flatDetail['entrances'] as $entrance) {
        $domophoneId = intval($entrance['domophoneId']);
        if ($domophone_id == $domophoneId) {
            $blocked = false;
            break;
        }
    }

    if ($blocked == false) {
        break;
    }
}

if (!$blocked) {
    response(200,"open");
    $domophone = $households->getDomophone($domophone_id);
    $model = loadDomophone($domophone["model"], $domophone["url"], $domophone["credentials"]);
    $model->open_door($door_id);
    // response();
} else {
    response(404, false, 'Не найдено', 'Услуга недоступна (договор заблокирован либо не оплачен)');
}