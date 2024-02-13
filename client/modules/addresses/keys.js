({
    menuItem: false,

    init: function () {
        if (AVAIL("subscribers", "keys")) {
            this.menuItem = leftSide("fab fa-fw fa-keycdn", i18n("addresses.superKeys"), "?#addresses.keys", "households");
        }
        moduleLoaded("addresses.keys", this);
    },
    
    renderKeys: function (params) {
        loadingStart();
        QUERY("subscribers", "keys", {
            by: params.by ? params.by : "0",
            query: params.query ? params.query : "0",
        }, true).
        fail(FAILPAGE).
        done(result => {
            cardTable({
                target: "#mainForm",
                title: {
                    caption: i18n("addresses.keys"),
                    button: {
                        caption: i18n("addresses.addKey"),
                        click: () => {
                            modules.addresses.keys.addKey(params);
                        },
                    },
                },
                edit: keyId => {
                    modules.addresses.keys.modifyKey(keyId, params);
                },
                columns: [
                    {
                        title: i18n("addresses.keyId"),
                        nowrap: true,
                    },
                    {
                        title: i18n("addresses.rfId"),
                        nowrap: true,
                    },
                    {
                        title: i18n("addresses.lastSeen"),
                        nowrap: true,
                    },
                    {
                        title: i18n("addresses.comments"),
                        fullWidth: true,
                    },
                ],
                rows: () => {
                    let rows = [];

                    for (let i in result.keys) {
                        rows.push({
                            uid: result.keys[i].keyId,
                            cols: [
                                {
                                    data: result.keys[i].keyId,
                                    nowrap: true,
                                },
                                {
                                    data: result.keys[i].rfId,
                                    nowrap: true,
                                },
                                {
                                    data: ttDate(result.keys[i].lastSeen),
                                    nowrap: true,
                                },
                                {
                                    data: result.keys[i].comments,
                                },
                            ],
                            dropDown: {
                                items: [
                                    {
                                        icon: "fas fa-trash-alt",
                                        title: i18n("addresses.deleteKey"),
                                        class: "text-danger",
                                        click: keyId => {
                                            modules.addresses.keys.removeKey(keyId, params);
                                        },
                                    },
                                ],
                            },
                        });
                    }

                    return rows;
                },
            }).show();
    
            loadingDone();
        });
    },

    addKey: function (params) {
        cardForm({
            title: i18n("addresses.addKey"),
            footer: true,
            borderless: true,
            topApply: true,
            apply: i18n("add"),
            fields: [
                {
                    id: "rfId",
                    type: "text",
                    title: i18n("addresses.key"),
                    placeholder: "00000000ABCDEF",
                    validate: (v) => {
                        return new RegExp(config.regExp.rfid).test(v);
                    }
                },
                {
                    id: "comments",
                    type: "text",
                    title: i18n("addresses.comments"),
                    placeholder: i18n("addresses.comments"),
                },
            ],
            callback: function (result) {
                result.accessType = params.by ? params.by : "0";
                result.accessTo = params.query ? params.query : "0";
                loadingStart();
                POST("subscribers", "key", false, result).
                fail(FAIL).
                done(() => {
                    message(i18n("addresses.keyWasAdded"));
                }).
                always(() => {
                    modules.addresses.keys.route(params);
                });
            },
        }).show();
    },

    removeKey: function (keyId, params) {

    },

    modifyKey: function (keyId, params) {

    },

    route: function (params) {
        $("#altForm").hide();

        if (modules.addresses.keys.menuItem) {
            $("#" + modules.addresses.keys.menuItem).children().first().attr("href", "?#addresses.keys&_=" + Math.random());
        }

        if (params.backStr && params.back) {
            subTop(`<a href="?#${params.back}">${params.backStr}</a>`);
        }

        if (parseInt(params.by)) {
            document.title = i18n("windowTitle") + " :: " + i18n("addresses.objectKeys", i18n("addresses.keysType" + parseInt(params.by)));
        } else {
            document.title = i18n("windowTitle") + " :: " + i18n("addresses.superKeys");
        }

        modules.addresses.keys.renderKeys(params);
    },
}).init();