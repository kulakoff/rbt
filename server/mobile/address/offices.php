<?php

/**
 * @api {post} /address/offices адреса офисов ООО "ЛанТа"
 * @apiVersion 1.0.0
 * @apiDescription **[метод готов]**
 *
 * @apiGroup Address
 *
 * @apiHeader {String} authorization токен авторизации
 *
 * @apiSuccess {Object[]} - массив адресов
 * @apiSuccess {Number} -.lat широта
 * @apiSuccess {Number} -.lon долгота
 * @apiSuccess {String} -.address адрес
 * @apiSuccess {String} -.opening время работы
 */

    auth();
    response(200, [['address' => 'Test', 'lat' => 50.730641, 'lon' => 43.452340], 'opening' => 'без выходных']);
