({
    init: function () {
        // submodule - module<dot>submodule
        moduleLoaded("addresses.subscribers", this);
    },

    doAddSubscriber: function (subscriber) {
        loadingStart();
        POST("subscribers", "subscriber", false, subscriber).
        fail(FAIL).
        done(() => {
            message(i18n("addresses.subscriberWasAdded"));
        }).
        always(() => {
            let [ route, params, hash ] = hashParse();

            modules.addresses.subscribers.route(params);
        });
    },

    doModifySubscriber: function (subscriber) {
        loadingStart();
        PUT("subscribers", "subscriber", subscriber.subscriberId, subscriber).
        fail(FAIL).
        done(() => {
            message(i18n("addresses.subscriberWasChanged"));
        }).
        always(() => {
            let [ route, params, hash ] = hashParse();

            modules.addresses.subscribers.route(params);
        });
    },

    doDeleteSubscriber: function (subscriberId) {
        loadingStart();
        DELETE("subscribers", "subscriber", subscriberId).
        fail(FAIL).
        done(() => {
            message(i18n("addresses.subscriberWasDeleted"));
        }).
        always(() => {
            let [ route, params, hash ] = hashParse();

            modules.addresses.subscribers.route(params);
        });
    },

    addSubscriber: function () {
        cardForm({
            title: i18n("addresses.addSubscriber"),
            footer: true,
            borderless: true,
            topApply: true,
            apply: i18n("add"),
            fields: [
                {
                    id: "mobile",
                    type: "text",
                    title: i18n("addresses.mobile"),
                    placeholder: i18n("addresses.mobile"),
                    validate: (v) => {
                        return $.trim(v) !== "";
                    }
                },
                {
                    id: "subscriberName",
                    type: "text",
                    title: i18n("addresses.subscriberName"),
                    placeholder: i18n("addresses.subscriberName"),
                },
                {
                    id: "subscriberPatronymic",
                    type: "text",
                    title: i18n("addresses.subscriberPatronymic"),
                    placeholder: i18n("addresses.subscriberPatronymic"),
                },
            ],
            callback: function (result) {
                let [ route, params, hash ] = hashParse();

                if (params.flatId) {
                    result.flatId = params.flatId;
                }

                modules.addresses.subscribers.doAddSubscriber(result);
            },
        }).show();
    },

    modifySubscriber: function (subscriberId, list) {
        let subscriber = false;

        for (let i in list) {
            if (list[i].subscriberId == subscriberId) {
                subscriber = list[i];
                break;
            }
        }

        if (subscriber) {

            let flats = [];

            for (let i in subscriber.flats) {
                let owner;

                try {
                    owner = subscriber.flats[i].role.toString() !== "1";
                } catch (e) {
                    owner = true;
                }

                let link = `<a href='#addresses.subscribers&flatId=${subscriber.flats[i].flatId}&houseId=${subscriber.flats[i].house.houseId}&flat=${subscriber.flats[i].flat}&house=${encodeURIComponent(subscriber.flats[i].house.houseFull)}'><i class='fas fa-fw fa-xs fa-link'></i></a>`;
                let role = `
                    <div class="custom-control custom-checkbox mb-0">
                        <input type="checkbox" class="custom-control-input" id="subscriber-role-flat-${subscriber.flats[i].flatId}"${owner?" checked":""}>
                        <label class="custom-control-label form-check-label" for="subscriber-role-flat-${subscriber.flats[i].flatId}">${i18n("addresses.subscriberFlatOwner")}</label>
                    </div>
                `;
                flats.push({
                    "id": subscriber.flats[i].flatId,
                    "text": subscriber.flats[i].house.houseFull + ", " + subscriber.flats[i].flat + " " + link,
                    "checked": true,
                    "append": role,
                });
            }

            cardForm({
                title: i18n("addresses.editSubscriber"),
                footer: true,
                borderless: true,
                topApply: true,
                apply: i18n("edit"),
                delete: i18n("addresses.deleteSubscriber"),
                size: "lg",
                fields: [
                    {
                        id: "subscriberId",
                        type: "text",
                        title: i18n("addresses.subscriberId"),
                        readonly: true,
                        value: subscriber.subscriberId,
                    },
                    {
                        id: "mobile",
                        type: "text",
                        title: i18n("addresses.mobile"),
                        placeholder: i18n("addresses.mobile"),
                        validate: (v) => {
                            return $.trim(v) !== "";
                        },
                        value: subscriber.mobile,
                    },
                    {
                        id: "subscriberName",
                        type: "text",
                        title: i18n("addresses.subscriberName"),
                        placeholder: i18n("addresses.subscriberName"),
                        value: subscriber.subscriberName,
                    },
                    {
                        id: "subscriberPatronymic",
                        type: "text",
                        title: i18n("addresses.subscriberPatronymic"),
                        placeholder: i18n("addresses.subscriberPatronymic"),
                        value: subscriber.subscriberPatronymic,
                    },
                    {
                        id: "flats",
                        type: "multiselect",
                        title: i18n("addresses.subscriberFlats"),
                        options: flats,
                    },
                    {
                        id: "authToken",
                        type: "text",
                        title: i18n("addresses.authToken"),
                        value: subscriber.authToken,
                        readonly: true,
                    },
                    {
                        id: "pushToken",
                        type: "text",
                        title: i18n("addresses.pushToken"),
                        value: subscriber.pushToken,
                        readonly: true,
                    },
                ],
                callback: function (result) {
                    if (result.delete === "yes") {
                        modules.addresses.subscribers.deleteSubscriber(subscriberId);
                    } else {
                        let [ route, params, hash ] = hashParse();

                        if (params.flatId) {
                            result.flatId = params.flatId;
                        }

                        let f = {};

                        for (let i in result.flats) {
                            f[result.flats[i]] = $("#subscriber-role-flat-" + result.flats[i]).prop("checked");
                        }

                        result.flats = f;

                        modules.addresses.subscribers.doModifySubscriber(result);
                    }
                },
            }).show();
        } else {
            error(i18n("addresses.subscriberNotFound"));
        }
    },

    deleteSubscriber: function (subscriberId) {
        mConfirm(i18n("addresses.confirmDeleteSubscriber", subscriberId.toString()), i18n("confirm"), `danger:${i18n("addresses.deleteSubscriber")}`, () => {
            modules.addresses.subscribers.doDeleteSubscriber(subscriberId);
        });
    },

    renderSubscribers: function (list, formTarget) {
        loadingStart();

        let [ route, params, hash ] = hashParse();

        cardTable({
            target: formTarget,
            title: {
                caption: i18n("addresses.subscribers"),
                button: {
                    caption: i18n("addresses.addSubscriber"),
                    click: modules.addresses.subscribers.addSubscriber,
                },
            },
            edit: subscriberId => {
                modules.addresses.subscribers.modifySubscriber(subscriberId, list);
            },
            columns: [
                {
                    title: i18n("addresses.subscriberId"),
                },
                {
                    title: i18n("addresses.mobile"),
                    nowrap: true,
                    fullWidth: true,
                },
                {
                    title: i18n("addresses.subscriberFlatOwner"),
                },
            ],
            rows: () => {
                let rows = [];

                for (let i in list) {
                    let owner;

                    for (let j in list[i].flats) {
                        if (list[i].flats[j].flatId == params.flatId) {
                            try {
                                owner = list[i].flats[j].role.toString() !== "1";
                            } catch (e) {
                                owner = true;
                            }
                        }
                    }

                    rows.push({
                        uid: list[i].subscriberId,
                        cols: [
                            {
                                data: list[i].subscriberId,
                            },
                            {
                                data: list[i].mobile,
                            },
                            {
                                data: owner?i18n("yes"):i18n("no"),
                            },
                        ],
                    });
                }

                return rows;
            },
        }).show();

        loadingDone();
    },

    renderKeys: function (list, formTarget) {
        loadingStart();

        cardTable({
            target: formTarget,
            title: {
                caption: i18n("addresses.keys"),
                button: {
                    caption: i18n("addresses.addSubscribers"),
                    click: modules.addresses.subscribers.addKey,
                },
            },
            edit: keyId => {
                modules.addresses.subscribers.modifyKey(keyId, list);
            },
            columns: [
                {
                    title: i18n("addresses.keyId"),
                },
                {
                    title: i18n("addresses.rfId"),
                    nowrap: true,
                    fullWidth: true,
                },
            ],
            rows: () => {
                let rows = [];

                for (let i in list) {
                    rows.push({
                        uid: list[i].rfId,
                        cols: [
                            {
                                data: list[i].rfId,
                            },
                            {
                                data: list[i].keyId,
                            },
                        ],
                    });
                }

                return rows;
            },
        }).show();

        loadingDone();
    },

    route: function (params) {
        if (params.flat) {
            subTop(params.house + ", " + params.flat);

            QUERY("subscribers", "subscribers", {
                by: "flat",
                query: params.flatId,
            }).done(responseSubscribers => {
                QUERY("subscribers", "keys", {
                    by: "flat",
                    query: params.flatId,
                }).done(responseKeys => {
                    modules.addresses.subscribers.renderSubscribers(responseSubscribers.subscribers, "#mainForm");
                    modules.addresses.subscribers.renderKeys(responseKeys.subscribers, "#altForm");
                }).
                fail(FAIL).
                fail(() => {
                    pageError();
                }).
                fail(loadingDone);
            }).
            fail(FAIL).
            fail(() => {
                pageError();
            }).
            fail(loadingDone);
        }
    }
}).init();
