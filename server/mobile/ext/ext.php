<?php

/**
 * @api {post} /ext/ext расширение
 * @apiVersion 1.0.0
 * @apiDescription **[нет верстки]**
 *
 * @apiGroup Ext
 *
 * @apiHeader {String} authorization токен авторизации
 *
 * @apiParam {String} extId идентификатор расширения
 * @apiParam {Object[]} params параметры передаваемые в расширение
 * @apiParam {String} params.id идентификатор
 * @apiParam {String} params.value значение
 *
 * @apiSuccess {Object} - страничка которую надо отобразить во вьюшке
 * @apiSuccess {String} -.basePath базовый путь (от которго должна была загрузиться страница)
 * @apiSuccess {String} -.code html страница
 * @apiSuccess {Number=1,2} [-.version=1] версия реализации web-расширений (1 - по умолчанию)
 */

    auth();
    response();
